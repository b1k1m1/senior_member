<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EventSeatingController extends Controller
{
    /**
     * Seating setup / overview screen.
     */
    public function index($eventId)
    {
        /*
        |--------------------------------------------------------------------------
        | Event
        |--------------------------------------------------------------------------
        */

        $event = DB::table('events')
            ->where('id', $eventId)
            ->first();

        if (!$event) {
            abort(404, 'Event not found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Existing Event Tables
        |--------------------------------------------------------------------------
        */

        $tables = DB::table('event_tables')
            ->where('event_id', $eventId)
            ->orderBy('table_no')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | All Seats For This Event
        |--------------------------------------------------------------------------
        */

        $tableIds = $tables
            ->pluck('id')
            ->filter()
            ->values();

        $seats = collect();

        if ($tableIds->isNotEmpty()) {
            $seats = DB::table('event_seats')
                ->whereIn('event_table_id', $tableIds)
                ->orderBy('event_table_id')
                ->orderBy('seat_no')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Confirmed Event Attendees
        |--------------------------------------------------------------------------
        */

        $attendees = DB::table('event_attendees')
            ->where('event_id', $eventId)
            ->where('status', 'CONFIRMED')
            ->orderBy('member_id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Attendee Members
        |--------------------------------------------------------------------------
        */

        $memberIds = $attendees
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values();

        $members = collect();

        if ($memberIds->isNotEmpty()) {
            $members = DB::table('members')
                ->whereIn('id', $memberIds)
                ->get()
                ->keyBy('id');
        }

        /*
        |--------------------------------------------------------------------------
        | Event Guests
        |--------------------------------------------------------------------------
        */

        $attendeeIds = $attendees
            ->pluck('id')
            ->filter()
            ->values();

        $guests = collect();

        if ($attendeeIds->isNotEmpty()) {
            $guests = DB::table('event_attendee_guests')
                ->whereIn('event_attendee_id', $attendeeIds)
                ->orderBy('event_attendee_id')
                ->orderBy('guest_no')
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Seating Summary
        |--------------------------------------------------------------------------
        */

        $totalTables = $tables->count();

        $totalSeats = $seats->count();

        $assignedMemberSeats = $seats
            ->whereNotNull('member_id')
            ->count();

        $assignedGuestSeats = $seats
            ->whereNotNull('event_guest_id')
            ->count();

        $assignedSeats = $assignedMemberSeats + $assignedGuestSeats;

        $availableSeats = $seats
            ->where('seat_status', 'AVAILABLE')
            ->whereNull('member_id')
            ->whereNull('event_guest_id')
            ->count();

        $blockedSeats = $seats
            ->where('seat_status', 'BLOCKED')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Number Of Expected People
        |--------------------------------------------------------------------------
        */

        $confirmedMembers = $attendees->count();

        $confirmedGuestCount = $attendees->sum(function ($attendee) {
            return (int) ($attendee->guests_count ?? 0);
        });

        $expectedPeople = $confirmedMembers + $confirmedGuestCount;

        /*
        |--------------------------------------------------------------------------
        | Unassigned Members
        |--------------------------------------------------------------------------
        */

        $seatedMemberIds = $seats
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values();

        $unassignedAttendees = $attendees
            ->filter(function ($attendee) use ($seatedMemberIds) {
                return !$seatedMemberIds->contains($attendee->member_id);
            })
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('admin.events.seating.index', compact(
            'event',
            'tables',
            'seats',
            'attendees',
            'members',
            'guests',
            'totalTables',
            'totalSeats',
            'assignedSeats',
            'assignedMemberSeats',
            'assignedGuestSeats',
            'availableSeats',
            'blockedSeats',
            'confirmedMembers',
            'confirmedGuestCount',
            'expectedPeople',
            'unassignedAttendees'
        ));
    }

    /**
     * Generate tables and seats for an event.
     */
    public function generate(Request $request, $eventId)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Event
        |--------------------------------------------------------------------------
        */

        $event = DB::table('events')
            ->where('id', $eventId)
            ->first();

        if (!$event) {
            abort(404, 'Event not found.');
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Input
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'number_of_tables' => [
                'required',
                'integer',
                'min:1',
                'max:200',
            ],

            'seats_per_table' => [
                'required',
                'integer',
                'min:1',
                'max:50',
            ],

            'table_shape' => [
                'required',
                'in:ROUND,RECTANGLE',
            ],
        ], [
            'number_of_tables.required' => 'Please enter the number of tables.',
            'number_of_tables.integer'  => 'Number of tables must be a whole number.',
            'number_of_tables.min'      => 'At least one table is required.',
            'number_of_tables.max'      => 'Maximum 200 tables are allowed.',

            'seats_per_table.required' => 'Please enter seats per table.',
            'seats_per_table.integer'  => 'Seats per table must be a whole number.',
            'seats_per_table.min'      => 'At least one seat per table is required.',
            'seats_per_table.max'      => 'Maximum 50 seats per table are allowed.',

            'table_shape.required' => 'Please select a table shape.',
            'table_shape.in'       => 'Invalid table shape selected.',
        ]);

        $numberOfTables = (int) $validated['number_of_tables'];
        $seatsPerTable  = (int) $validated['seats_per_table'];
        $tableShape     = $validated['table_shape'];

        /*
        |--------------------------------------------------------------------------
        | Prevent Accidental Duplicate Generation
        |--------------------------------------------------------------------------
        */

        $existingTableCount = DB::table('event_tables')
            ->where('event_id', $eventId)
            ->count();

        if ($existingTableCount > 0) {
            return redirect()
                ->route('events.seating.index', $eventId)
                ->with(
                    'error',
                    'Tables have already been generated for this event.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Tables + Seats
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();

        try {

            $userId = Auth::id();

            for ($tableNo = 1; $tableNo <= $numberOfTables; $tableNo++) {

                /*
                |--------------------------------------------------------------------------
                | Create Table
                |--------------------------------------------------------------------------
                */

                $tableId = DB::table('event_tables')->insertGetId([
                    'event_id'    => $eventId,
                    'table_no'    => $tableNo,
                    'table_name'  => 'Table ' . $tableNo,
                    'capacity'    => $seatsPerTable,
                    'table_shape' => $tableShape,
                    'position_x'  => null,
                    'position_y'  => null,
                    'notes'       => null,
                    'created_by'  => $userId,
                    'updated_by'  => $userId,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);

                /*
                |--------------------------------------------------------------------------
                | Prepare Seats
                |--------------------------------------------------------------------------
                */

                $seatRows = [];

                for ($seatNo = 1; $seatNo <= $seatsPerTable; $seatNo++) {

                    $seatRows[] = [
                        'event_table_id' => $tableId,
                        'seat_no'        => $seatNo,
                        'member_id'      => null,
                        'event_guest_id' => null,
                        'seat_status'    => 'AVAILABLE',
                        'notes'          => null,
                        'created_by'     => $userId,
                        'updated_by'     => $userId,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ];
                }

                /*
                |--------------------------------------------------------------------------
                | Insert Seats
                |--------------------------------------------------------------------------
                */

                if (!empty($seatRows)) {
                    DB::table('event_seats')->insert($seatRows);
                }
            }

            DB::commit();

            $totalSeats = $numberOfTables * $seatsPerTable;

            return redirect()
                ->route('events.seating.index', $eventId)
                ->with(
                    'success',
                    $numberOfTables .
                        ' tables and ' .
                        $totalSeats .
                        ' seats generated successfully.'
                );
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return redirect()
                ->route('events.seating.index', $eventId)
                ->with(
                    'error',
                    'Unable to generate seating tables. Please try again.'
                );
        }
    }

    /**
     * View one table and its seats.
     */
    public function showTable($eventId, $tableId)
    {
        /*
        |--------------------------------------------------------------------------
        | Event
        |--------------------------------------------------------------------------
        */

        $event = DB::table('events')
            ->where('id', $eventId)
            ->first();

        if (!$event) {
            abort(404, 'Event not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | Event Table
        |--------------------------------------------------------------------------
        */

        $table = DB::table('event_tables')
            ->where('id', $tableId)
            ->where('event_id', $eventId)
            ->first();

        if (!$table) {
            abort(404, 'Table not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | Seats For This Table
        |--------------------------------------------------------------------------
        */

        $seats = DB::table('event_seats')
            ->where('event_table_id', $tableId)
            ->orderBy('seat_no')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Confirmed Attendees
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        | Seating is based on CONFIRMED status only.
        |
        | We intentionally DO NOT check amount_paid here because in a COMBINED
        | couple registration the spouse may correctly have amount_paid = 0.
        |--------------------------------------------------------------------------
        */

        $attendees = DB::table('event_attendees')
            ->where('event_id', $eventId)
            ->where('status', 'CONFIRMED')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Attendees Keyed By ID
        |--------------------------------------------------------------------------
        */

        $attendeesById = $attendees
            ->keyBy('id');


        /*
        |--------------------------------------------------------------------------
        | Confirmed Member IDs
        |--------------------------------------------------------------------------
        */

        $attendeeMemberIds = $attendees
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Confirmed Members
        |--------------------------------------------------------------------------
        */

        $members = collect();

        if ($attendeeMemberIds->isNotEmpty()) {

            $members = DB::table('members')
                ->whereIn('id', $attendeeMemberIds)
                ->where('status', 'ACTIVE')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->keyBy('id');
        }


        /*
        |--------------------------------------------------------------------------
        | All Event Table IDs
        |--------------------------------------------------------------------------
        */

        $eventTableIds = DB::table('event_tables')
            ->where('event_id', $eventId)
            ->pluck('id');


        /*
        |--------------------------------------------------------------------------
        | Members Already Seated Anywhere In This Event
        |--------------------------------------------------------------------------
        */

        $seatedMemberIds = collect();

        if ($eventTableIds->isNotEmpty()) {

            $seatedMemberIds = DB::table('event_seats')
                ->whereIn('event_table_id', $eventTableIds)
                ->whereNotNull('member_id')
                ->pluck('member_id')
                ->unique()
                ->values();
        }


        /*
        |--------------------------------------------------------------------------
        | Guests Already Seated Anywhere In This Event
        |--------------------------------------------------------------------------
        */

        $seatedGuestIds = collect();

        if ($eventTableIds->isNotEmpty()) {

            $seatedGuestIds = DB::table('event_seats')
                ->whereIn('event_table_id', $eventTableIds)
                ->whereNotNull('event_guest_id')
                ->pluck('event_guest_id')
                ->unique()
                ->values();
        }


        /*
        |--------------------------------------------------------------------------
        | Members Available For Assignment
        |--------------------------------------------------------------------------
        */

        $availableMembers = $members
            ->filter(function ($member) use ($seatedMemberIds) {

                return !$seatedMemberIds
                    ->contains($member->id);
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Confirmed Attendee IDs
        |--------------------------------------------------------------------------
        */

        $confirmedAttendeeIds = $attendees
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Guests Belonging To Confirmed Attendees
        |--------------------------------------------------------------------------
        */

        $allGuests = collect();

        if ($confirmedAttendeeIds->isNotEmpty()) {

            $allGuests = DB::table('event_attendee_guests')
                ->whereIn(
                    'event_attendee_id',
                    $confirmedAttendeeIds
                )
                ->orderBy('event_attendee_id')
                ->orderBy('guest_no')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Guests Available For Assignment
        |--------------------------------------------------------------------------
        */

        $availableGuests = $allGuests
            ->filter(function ($guest) use ($seatedGuestIds) {

                return !$seatedGuestIds
                    ->contains($guest->id);
            })
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Members Already Assigned To Current Table
        |--------------------------------------------------------------------------
        */

        $currentTableMemberIds = $seats
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values();


        $seatMembers = collect();


        if ($currentTableMemberIds->isNotEmpty()) {

            $seatMembers = DB::table('members')
                ->whereIn(
                    'id',
                    $currentTableMemberIds
                )
                ->get()
                ->keyBy('id');
        }


        /*
        |--------------------------------------------------------------------------
        | Guests Already Assigned To Current Table
        |--------------------------------------------------------------------------
        */

        $currentTableGuestIds = $seats
            ->pluck('event_guest_id')
            ->filter()
            ->unique()
            ->values();


        $guests = collect();


        if ($currentTableGuestIds->isNotEmpty()) {

            $guests = DB::table('event_attendee_guests')
                ->whereIn(
                    'id',
                    $currentTableGuestIds
                )
                ->get()
                ->keyBy('id');
        }


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.events.seating.table',
            compact(
                'event',
                'table',
                'seats',
                'attendees',
                'attendeesById',
                'members',
                'availableMembers',
                'availableGuests',
                'seatMembers',
                'guests'
            )
        );
    } // End Method

    /**
     * Assign a member to a seat.
     */
    public function assignMember(Request $request, $eventId, $seatId)
    {
        $validated = $request->validate([
            'member_id' => [
                'required',
                'integer',
            ],
        ]);

        $memberId = (int) $validated['member_id'];

        /*
        |--------------------------------------------------------------------------
        | Validate Seat
        |--------------------------------------------------------------------------
        */

        $seat = DB::table('event_seats')
            ->where('id', $seatId)
            ->first();

        if (!$seat) {
            abort(404, 'Seat not found.');
        }

        $eventTable = DB::table('event_tables')
            ->where('id', $seat->event_table_id)
            ->where('event_id', $eventId)
            ->first();

        if (!$eventTable) {
            abort(404, 'Invalid event table.');
        }

        if ($seat->seat_status === 'BLOCKED') {
            return back()->with(
                'error',
                'This seat is blocked and cannot be assigned.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Member Is Confirmed For Event
        |--------------------------------------------------------------------------
        */

        $attendee = DB::table('event_attendees')
            ->where('event_id', $eventId)
            ->where('member_id', $memberId)
            ->where('status', 'CONFIRMED')
            ->first();

        if (!$attendee) {
            return back()->with(
                'error',
                'This member is not a confirmed attendee for this event.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Member From Having Two Seats
        |--------------------------------------------------------------------------
        */

        $eventTables = DB::table('event_tables')
            ->where('event_id', $eventId)
            ->pluck('id');

        $existingSeat = DB::table('event_seats')
            ->whereIn('event_table_id', $eventTables)
            ->where('member_id', $memberId)
            ->where('id', '!=', $seatId)
            ->first();

        if ($existingSeat) {
            return back()->with(
                'error',
                'This member is already assigned to another seat.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Assign Seat
        |--------------------------------------------------------------------------
        */

        DB::table('event_seats')
            ->where('id', $seatId)
            ->update([
                'member_id'      => $memberId,
                'event_guest_id' => null,
                'seat_status'    => 'ASSIGNED',
                'updated_by'     => Auth::id(),
                'updated_at'     => now(),
            ]);

        return back()->with(
            'success',
            'Member assigned successfully.'
        );
    }

    /**
     * Assign a guest to a seat.
     */
    public function assignGuest(Request $request, $eventId, $seatId)
    {
        $validated = $request->validate([
            'event_guest_id' => [
                'required',
                'integer',
            ],
        ]);

        $guestId = (int) $validated['event_guest_id'];

        /*
        |--------------------------------------------------------------------------
        | Validate Seat
        |--------------------------------------------------------------------------
        */

        $seat = DB::table('event_seats')
            ->where('id', $seatId)
            ->first();

        if (!$seat) {
            abort(404, 'Seat not found.');
        }

        $eventTable = DB::table('event_tables')
            ->where('id', $seat->event_table_id)
            ->where('event_id', $eventId)
            ->first();

        if (!$eventTable) {
            abort(404, 'Invalid event table.');
        }

        if ($seat->seat_status === 'BLOCKED') {
            return back()->with(
                'error',
                'This seat is blocked and cannot be assigned.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Guest Belongs To This Event
        |--------------------------------------------------------------------------
        */

        $guest = DB::table('event_attendee_guests')
            ->where('id', $guestId)
            ->first();

        if (!$guest) {
            return back()->with(
                'error',
                'Guest not found.'
            );
        }

        $attendee = DB::table('event_attendees')
            ->where('id', $guest->event_attendee_id)
            ->where('event_id', $eventId)
            ->where('status', 'CONFIRMED')
            ->first();

        if (!$attendee) {
            return back()->with(
                'error',
                'This guest does not belong to a confirmed attendee for this event.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate Guest Assignment
        |--------------------------------------------------------------------------
        */

        $eventTables = DB::table('event_tables')
            ->where('event_id', $eventId)
            ->pluck('id');

        $existingSeat = DB::table('event_seats')
            ->whereIn('event_table_id', $eventTables)
            ->where('event_guest_id', $guestId)
            ->where('id', '!=', $seatId)
            ->first();

        if ($existingSeat) {
            return back()->with(
                'error',
                'This guest is already assigned to another seat.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Assign Guest
        |--------------------------------------------------------------------------
        */

        DB::table('event_seats')
            ->where('id', $seatId)
            ->update([
                'member_id'      => null,
                'event_guest_id' => $guestId,
                'seat_status'    => 'ASSIGNED',
                'updated_by'     => Auth::id(),
                'updated_at'     => now(),
            ]);

        return back()->with(
            'success',
            'Guest assigned successfully.'
        );
    }

    /**
     * Clear a seat assignment.
     */
    public function clearSeat($eventId, $seatId)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Seat
        |--------------------------------------------------------------------------
        */

        $seat = DB::table('event_seats')
            ->where('id', $seatId)
            ->first();

        if (!$seat) {
            abort(404, 'Seat not found.');
        }

        $eventTable = DB::table('event_tables')
            ->where('id', $seat->event_table_id)
            ->where('event_id', $eventId)
            ->first();

        if (!$eventTable) {
            abort(404, 'Invalid event table.');
        }

        if ($seat->seat_status === 'BLOCKED') {
            return back()->with(
                'error',
                'Blocked seats cannot be cleared using this action.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Clear Assignment
        |--------------------------------------------------------------------------
        */

        DB::table('event_seats')
            ->where('id', $seatId)
            ->update([
                'member_id'      => null,
                'event_guest_id' => null,
                'seat_status'    => 'AVAILABLE',
                'updated_by'     => Auth::id(),
                'updated_at'     => now(),
            ]);

        return back()->with(
            'success',
            'Seat assignment removed successfully.'
        );
    } // End Method

    public function hall($eventId)
    {
        /*
        |--------------------------------------------------------------------------
        | Event
        |--------------------------------------------------------------------------
        */

        $event = DB::table('events')
            ->where('id', $eventId)
            ->first();

        if (!$event) {
            abort(404, 'Event not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | Event Tables
        |--------------------------------------------------------------------------
        */

        $tables = DB::table('event_tables')
            ->where('event_id', $eventId)
            ->orderBy('table_no')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Table IDs
        |--------------------------------------------------------------------------
        */

        $tableIds = $tables
            ->pluck('id')
            ->filter()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Seats
        |--------------------------------------------------------------------------
        */

        $seats = collect();

        if ($tableIds->isNotEmpty()) {

            $seats = DB::table('event_seats')
                ->whereIn('event_table_id', $tableIds)
                ->orderBy('event_table_id')
                ->orderBy('seat_no')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Member IDs Currently Seated
        |--------------------------------------------------------------------------
        */

        $memberIds = $seats
            ->pluck('member_id')
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Members
        |--------------------------------------------------------------------------
        */

        $members = collect();

        if ($memberIds->isNotEmpty()) {

            $members = DB::table('members')
                ->whereIn('id', $memberIds)
                ->get()
                ->keyBy('id');
        }


        /*
        |--------------------------------------------------------------------------
        | Guest IDs Currently Seated
        |--------------------------------------------------------------------------
        */

        $guestIds = $seats
            ->pluck('event_guest_id')
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Guests
        |--------------------------------------------------------------------------
        */

        $guests = collect();

        if ($guestIds->isNotEmpty()) {

            $guests = DB::table('event_attendee_guests')
                ->whereIn('id', $guestIds)
                ->get()
                ->keyBy('id');
        }


        /*
        |--------------------------------------------------------------------------
        | Overall Summary
        |--------------------------------------------------------------------------
        */

        $totalTables = $tables->count();

        $totalSeats = $seats->count();

        $assignedSeats = $seats
            ->filter(function ($seat) {

                return !is_null($seat->member_id)
                    || !is_null($seat->event_guest_id);
            })
            ->count();

        $availableSeats = $seats
            ->filter(function ($seat) {

                return $seat->seat_status === 'AVAILABLE'
                    && is_null($seat->member_id)
                    && is_null($seat->event_guest_id);
            })
            ->count();

        $blockedSeats = $seats
            ->where('seat_status', 'BLOCKED')
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Return Hall Layout
        |--------------------------------------------------------------------------
        */

        return view('admin.events.seating.hall', compact(
            'event',
            'tables',
            'seats',
            'members',
            'guests',
            'totalTables',
            'totalSeats',
            'assignedSeats',
            'availableSeats',
            'blockedSeats'
        ));
    } // End Method

    public function saveHallPositions(Request $request, $eventId)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Event
        |--------------------------------------------------------------------------
        */

        $event = DB::table('events')
            ->where('id', $eventId)
            ->first();

        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'positions' => [
                'required',
                'array',
            ],

            'positions.*.table_id' => [
                'required',
                'integer',
            ],

            'positions.*.position_x' => [
                'required',
                'integer',
                'min:0',
                'max:5000',
            ],

            'positions.*.position_y' => [
                'required',
                'integer',
                'min:0',
                'max:5000',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Get Valid Table IDs For Event
        |--------------------------------------------------------------------------
        */

        $validTableIds = DB::table('event_tables')
            ->where('event_id', $eventId)
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->toArray();


        /*
        |--------------------------------------------------------------------------
        | Save Positions
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();

        try {

            foreach ($validated['positions'] as $position) {

                $tableId = (int) $position['table_id'];

                /*
                |--------------------------------------------------------------------------
                | Security Check
                |--------------------------------------------------------------------------
                |
                | Make sure table actually belongs to this event.
                |
                */

                if (!in_array($tableId, $validTableIds, true)) {
                    continue;
                }


                DB::table('event_tables')
                    ->where('id', $tableId)
                    ->where('event_id', $eventId)
                    ->update([
                        'position_x' => (int) $position['position_x'],
                        'position_y' => (int) $position['position_y'],
                        'updated_by' => Auth::id(),
                        'updated_at' => now(),
                    ]);
            }


            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'Hall layout saved successfully.',
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);


            return response()->json([
                'success' => false,
                'message' => 'Unable to save hall layout.',
            ], 500);
        }
    } // End Method

    public function partyDetails($eventId, $memberId)
    {
        /*
        |--------------------------------------------------------------------------
        | Event
        |--------------------------------------------------------------------------
        */

        $event = DB::table('events')
            ->where('id', $eventId)
            ->first();

        if (!$event) {
            abort(404, 'Event not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Member
        |--------------------------------------------------------------------------
        */

        $member = DB::table('members')
            ->where('id', $memberId)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$member) {
            return response()->json([
                'success' => false,
                'message' => 'Member not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Member Must Be Confirmed
        |--------------------------------------------------------------------------
        */

        $primaryAttendee = DB::table('event_attendees')
            ->where('event_id', $eventId)
            ->where('member_id', $memberId)
            ->where('status', 'CONFIRMED')
            ->first();

        if (!$primaryAttendee) {
            return response()->json([
                'success' => false,
                'message' => 'This member is not a confirmed attendee.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Party Member IDs
        |--------------------------------------------------------------------------
        */

        $partyMemberIds = collect([
            (int) $member->id,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Find Confirmed Spouse / Associated Member
        |--------------------------------------------------------------------------
        */

        $spouseMember = null;
        $spouseAttendee = null;

        if (!empty($member->receipt_no)) {

            $possibleSpouse = DB::table('members')
                ->where('receipt_no', $member->receipt_no)
                ->where('id', '!=', $member->id)
                ->where('status', 'ACTIVE')
                ->orderBy('id')
                ->first();

            if ($possibleSpouse) {

                $possibleSpouseAttendee = DB::table('event_attendees')
                    ->where('event_id', $eventId)
                    ->where('member_id', $possibleSpouse->id)
                    ->where('status', 'CONFIRMED')
                    ->first();

                if ($possibleSpouseAttendee) {

                    $spouseMember = $possibleSpouse;
                    $spouseAttendee = $possibleSpouseAttendee;

                    $partyMemberIds->push(
                        (int) $spouseMember->id
                    );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Party Attendees
        |--------------------------------------------------------------------------
        */

        $partyAttendees = DB::table('event_attendees')
            ->where('event_id', $eventId)
            ->where('status', 'CONFIRMED')
            ->whereIn(
                'member_id',
                $partyMemberIds->unique()->values()
            )
            ->get();


        $partyAttendeeIds = $partyAttendees
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Named Guests
        |--------------------------------------------------------------------------
        */

        $partyGuests = collect();

        if ($partyAttendeeIds->isNotEmpty()) {

            $partyGuests = DB::table('event_attendee_guests')
                ->whereIn(
                    'event_attendee_id',
                    $partyAttendeeIds
                )
                ->orderBy('event_attendee_id')
                ->orderBy('guest_no')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Event Tables
        |--------------------------------------------------------------------------
        */

        $eventTables = DB::table('event_tables')
            ->where('event_id', $eventId)
            ->get()
            ->keyBy('id');

        $eventTableIds = $eventTables
            ->pluck('id')
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Existing Seat Assignments
        |--------------------------------------------------------------------------
        */

        $memberSeats = collect();
        $guestSeats = collect();

        if ($eventTableIds->isNotEmpty()) {

            $memberSeats = DB::table('event_seats')
                ->whereIn('event_table_id', $eventTableIds)
                ->whereIn(
                    'member_id',
                    $partyMemberIds->unique()->values()
                )
                ->whereNotNull('member_id')
                ->get()
                ->keyBy('member_id');


            if ($partyGuests->isNotEmpty()) {

                $guestSeats = DB::table('event_seats')
                    ->whereIn('event_table_id', $eventTableIds)
                    ->whereIn(
                        'event_guest_id',
                        $partyGuests->pluck('id')
                    )
                    ->whereNotNull('event_guest_id')
                    ->get()
                    ->keyBy('event_guest_id');
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Helper - Table Label
        |--------------------------------------------------------------------------
        */

        $getTableLabel = function ($eventTableId) use ($eventTables) {

            if (!$eventTableId) {
                return null;
            }

            $table = $eventTables->get($eventTableId);

            if (!$table) {
                return null;
            }

            if (!empty($table->table_name)) {
                return $table->table_name;
            }

            return 'Table ' . $table->table_no;
        };


        /*
        |--------------------------------------------------------------------------
        | Build Member Party List
        |--------------------------------------------------------------------------
        */

        $partyMembers = collect();


        /*
        |--------------------------------------------------------------------------
        | Primary Member
        |--------------------------------------------------------------------------
        */

        $primarySeat = $memberSeats->get(
            $member->id
        );

        $partyMembers->push([
            'type' => 'MEMBER',

            'id' => (int) $member->id,

            'member_no' => $member->member_no,

            'name' => trim(
                $member->first_name . ' ' .
                    $member->last_name
            ),

            'relationship' => 'Member',

            'seated' => $primarySeat ? true : false,

            'seat_id' => $primarySeat
                ? (int) $primarySeat->id
                : null,

            'event_table_id' => $primarySeat
                ? (int) $primarySeat->event_table_id
                : null,

            'table_label' => $primarySeat
                ? $getTableLabel($primarySeat->event_table_id)
                : null,

            'seat_no' => $primarySeat
                ? (int) $primarySeat->seat_no
                : null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Spouse
        |--------------------------------------------------------------------------
        */

        if ($spouseMember) {

            $spouseSeat = $memberSeats->get(
                $spouseMember->id
            );

            $partyMembers->push([
                'type' => 'MEMBER',

                'id' => (int) $spouseMember->id,

                'member_no' => $spouseMember->member_no,

                'name' => trim(
                    $spouseMember->first_name . ' ' .
                        $spouseMember->last_name
                ),

                'relationship' => 'Spouse',

                'seated' => $spouseSeat ? true : false,

                'seat_id' => $spouseSeat
                    ? (int) $spouseSeat->id
                    : null,

                'event_table_id' => $spouseSeat
                    ? (int) $spouseSeat->event_table_id
                    : null,

                'table_label' => $spouseSeat
                    ? $getTableLabel($spouseSeat->event_table_id)
                    : null,

                'seat_no' => $spouseSeat
                    ? (int) $spouseSeat->seat_no
                    : null,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Guests
        |--------------------------------------------------------------------------
        */

        $guestRows = collect();

        foreach ($partyGuests as $guest) {

            $guestSeat = $guestSeats->get(
                $guest->id
            );

            $guestRows->push([
                'type' => 'GUEST',

                'id' => (int) $guest->id,

                'name' => $guest->guest_name ?: 'Guest',

                'guest_type' => $guest->guest_type,

                'relationship' => ucfirst(
                    strtolower($guest->guest_type)
                ),

                'seated' => $guestSeat ? true : false,

                'seat_id' => $guestSeat
                    ? (int) $guestSeat->id
                    : null,

                'event_table_id' => $guestSeat
                    ? (int) $guestSeat->event_table_id
                    : null,

                'table_label' => $guestSeat
                    ? $getTableLabel($guestSeat->event_table_id)
                    : null,

                'seat_no' => $guestSeat
                    ? (int) $guestSeat->seat_no
                    : null,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Party Counts
        |--------------------------------------------------------------------------
        */

        $totalPartySize =
            $partyMembers->count() +
            $guestRows->count();


        $alreadySeated =
            $partyMembers
            ->where('seated', true)
            ->count()
            +
            $guestRows
            ->where('seated', true)
            ->count();


        $needsSeat =
            $totalPartySize -
            $alreadySeated;


        /*
        |--------------------------------------------------------------------------
        | Return Party Information
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,

            'party' => [

                'primary_member_id' =>
                (int) $member->id,

                'primary_member_no' =>
                $member->member_no,

                'primary_member_name' =>
                trim(
                    $member->first_name . ' ' .
                        $member->last_name
                ),

                'total_party_size' =>
                $totalPartySize,

                'already_seated' =>
                $alreadySeated,

                'needs_seat' =>
                $needsSeat,

                'members' =>
                $partyMembers->values(),

                'guests' =>
                $guestRows->values(),
            ],
        ]);
    } // End Method

    public function partyAvailableTables($eventId, $memberId)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Event
        |--------------------------------------------------------------------------
        */

        $event = DB::table('events')
            ->where('id', $eventId)
            ->first();

        if (!$event) {
            abort(404, 'Event not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | Validate Primary Member
        |--------------------------------------------------------------------------
        */

        $member = DB::table('members')
            ->where('id', $memberId)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$member) {

            return response()->json([
                'success' => false,
                'message' => 'Member not found.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Primary Must Be Confirmed
        |--------------------------------------------------------------------------
        */

        $primaryAttendee = DB::table('event_attendees')
            ->where('event_id', $eventId)
            ->where('member_id', $memberId)
            ->where('status', 'CONFIRMED')
            ->first();

        if (!$primaryAttendee) {

            return response()->json([
                'success' => false,
                'message' => 'Member is not a confirmed attendee.',
            ], 422);
        }


        /*
        |--------------------------------------------------------------------------
        | Party Member IDs
        |--------------------------------------------------------------------------
        */

        $partyMemberIds = collect([
            (int) $memberId,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Find Confirmed Spouse
        |--------------------------------------------------------------------------
        */

        if (!empty($member->receipt_no)) {

            $possibleSpouse = DB::table('members')
                ->where('receipt_no', $member->receipt_no)
                ->where('id', '!=', $member->id)
                ->where('status', 'ACTIVE')
                ->orderBy('id')
                ->first();

            if ($possibleSpouse) {

                $spouseAttendee = DB::table('event_attendees')
                    ->where('event_id', $eventId)
                    ->where('member_id', $possibleSpouse->id)
                    ->where('status', 'CONFIRMED')
                    ->first();

                if ($spouseAttendee) {

                    $partyMemberIds->push(
                        (int) $possibleSpouse->id
                    );
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Party Attendees
        |--------------------------------------------------------------------------
        */

        $partyAttendees = DB::table('event_attendees')
            ->where('event_id', $eventId)
            ->where('status', 'CONFIRMED')
            ->whereIn(
                'member_id',
                $partyMemberIds
                    ->unique()
                    ->values()
            )
            ->get();


        $partyAttendeeIds = $partyAttendees
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Party Guests
        |--------------------------------------------------------------------------
        */

        $partyGuests = collect();

        if ($partyAttendeeIds->isNotEmpty()) {

            $partyGuests = DB::table('event_attendee_guests')
                ->whereIn(
                    'event_attendee_id',
                    $partyAttendeeIds
                )
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Event Tables
        |--------------------------------------------------------------------------
        */

        $tables = DB::table('event_tables')
            ->where('event_id', $eventId)
            ->orderBy('table_no')
            ->get();


        $tableIds = $tables
            ->pluck('id')
            ->filter()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Existing Party Member Seats
        |--------------------------------------------------------------------------
        */

        $seatedMemberIds = collect();

        if ($tableIds->isNotEmpty()) {

            $seatedMemberIds = DB::table('event_seats')
                ->whereIn('event_table_id', $tableIds)
                ->whereIn(
                    'member_id',
                    $partyMemberIds
                )
                ->whereNotNull('member_id')
                ->pluck('member_id')
                ->unique()
                ->values();
        }


        /*
        |--------------------------------------------------------------------------
        | Existing Party Guest Seats
        |--------------------------------------------------------------------------
        */

        $seatedGuestIds = collect();

        if (
            $tableIds->isNotEmpty() &&
            $partyGuests->isNotEmpty()
        ) {

            $seatedGuestIds = DB::table('event_seats')
                ->whereIn('event_table_id', $tableIds)
                ->whereIn(
                    'event_guest_id',
                    $partyGuests->pluck('id')
                )
                ->whereNotNull('event_guest_id')
                ->pluck('event_guest_id')
                ->unique()
                ->values();
        }


        /*
        |--------------------------------------------------------------------------
        | Number Still Needing Seats
        |--------------------------------------------------------------------------
        */

        $totalPartySize =
            $partyMemberIds
            ->unique()
            ->count()
            +
            $partyGuests
            ->count();


        $alreadySeated =
            $seatedMemberIds
            ->count()
            +
            $seatedGuestIds
            ->count();


        $needsSeat =
            $totalPartySize -
            $alreadySeated;


        /*
        |--------------------------------------------------------------------------
        | Build Table Availability
        |--------------------------------------------------------------------------
        */

        $availableTables = collect();


        foreach ($tables as $table) {

            $seats = DB::table('event_seats')
                ->where('event_table_id', $table->id)
                ->orderBy('seat_no')
                ->get();


            $availableSeats = $seats
                ->filter(function ($seat) {

                    return
                        $seat->seat_status === 'AVAILABLE'
                        &&
                        is_null($seat->member_id)
                        &&
                        is_null($seat->event_guest_id);
                })
                ->values();


            $availableCount =
                $availableSeats->count();


            $availableTables->push([

                'table_id' =>
                (int) $table->id,

                'table_no' =>
                (int) $table->table_no,

                'table_name' =>
                $table->table_name
                    ?: 'Table ' . $table->table_no,

                'capacity' =>
                (int) $table->capacity,

                'available_seats' =>
                $availableCount,

                'can_fit_party' =>
                $availableCount >= $needsSeat,

                'seat_numbers' =>
                $availableSeats
                    ->pluck('seat_no')
                    ->values(),

            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Return
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'party' => [

                'total_party_size' =>
                $totalPartySize,

                'already_seated' =>
                $alreadySeated,

                'needs_seat' =>
                $needsSeat,

            ],

            'tables' =>
            $availableTables
                ->values(),

        ]);
    } // End Method

    public function seatPartyTogether(Request $request, $eventId, $memberId)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Request
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'table_id' => [
                'required',
                'integer',
            ],
        ]);

        $tableId = (int) $validated['table_id'];


        /*
        |--------------------------------------------------------------------------
        | Event
        |--------------------------------------------------------------------------
        */

        $event = DB::table('events')
            ->where('id', $eventId)
            ->first();

        if (!$event) {
            abort(404, 'Event not found.');
        }


        /*
        |--------------------------------------------------------------------------
        | Primary Member
        |--------------------------------------------------------------------------
        */

        $member = DB::table('members')
            ->where('id', $memberId)
            ->where('status', 'ACTIVE')
            ->first();

        if (!$member) {

            return back()->with(
                'error',
                'Member not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Primary Member Must Be Confirmed
        |--------------------------------------------------------------------------
        */

        $primaryAttendee = DB::table('event_attendees')
            ->where('event_id', $eventId)
            ->where('member_id', $memberId)
            ->where('status', 'CONFIRMED')
            ->first();

        if (!$primaryAttendee) {

            return back()->with(
                'error',
                'This member is not a confirmed attendee.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Table
        |--------------------------------------------------------------------------
        */

        $selectedTable = DB::table('event_tables')
            ->where('id', $tableId)
            ->where('event_id', $eventId)
            ->first();

        if (!$selectedTable) {

            return back()->with(
                'error',
                'Selected table is invalid.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Party Member IDs
        |--------------------------------------------------------------------------
        */

        $partyMemberIds = collect([
            (int) $member->id,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Find Confirmed Spouse
        |--------------------------------------------------------------------------
        */

        $spouseMember = null;

        if (!empty($member->receipt_no)) {

            $possibleSpouse = DB::table('members')
                ->where('receipt_no', $member->receipt_no)
                ->where('id', '!=', $member->id)
                ->where('status', 'ACTIVE')
                ->orderBy('id')
                ->first();

            if ($possibleSpouse) {

                $spouseAttendee = DB::table('event_attendees')
                    ->where('event_id', $eventId)
                    ->where('member_id', $possibleSpouse->id)
                    ->where('status', 'CONFIRMED')
                    ->first();

                if ($spouseAttendee) {

                    $spouseMember = $possibleSpouse;

                    $partyMemberIds->push(
                        (int) $spouseMember->id
                    );
                }
            }
        }


        $partyMemberIds = $partyMemberIds
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Party Attendees
        |--------------------------------------------------------------------------
        */

        $partyAttendees = DB::table('event_attendees')
            ->where('event_id', $eventId)
            ->where('status', 'CONFIRMED')
            ->whereIn(
                'member_id',
                $partyMemberIds
            )
            ->get();


        $partyAttendeeIds = $partyAttendees
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Party Guests
        |--------------------------------------------------------------------------
        */

        $partyGuests = collect();

        if ($partyAttendeeIds->isNotEmpty()) {

            $partyGuests = DB::table('event_attendee_guests')
                ->whereIn(
                    'event_attendee_id',
                    $partyAttendeeIds
                )
                ->orderBy('event_attendee_id')
                ->orderBy('guest_no')
                ->get();
        }


        /*
        |--------------------------------------------------------------------------
        | Event Table IDs
        |--------------------------------------------------------------------------
        */

        $eventTableIds = DB::table('event_tables')
            ->where('event_id', $eventId)
            ->pluck('id')
            ->filter()
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Transaction
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Lock Event Seats
            |--------------------------------------------------------------------------
            |
            | This protects the party assignment while seats are being allocated.
            |--------------------------------------------------------------------------
            */

            $eventSeats = DB::table('event_seats')
                ->whereIn(
                    'event_table_id',
                    $eventTableIds
                )
                ->lockForUpdate()
                ->get();


            /*
            |--------------------------------------------------------------------------
            | Members Already Seated
            |--------------------------------------------------------------------------
            */

            $seatedMemberIds = $eventSeats
                ->pluck('member_id')
                ->filter()
                ->unique()
                ->values();


            /*
            |--------------------------------------------------------------------------
            | Guests Already Seated
            |--------------------------------------------------------------------------
            */

            $seatedGuestIds = $eventSeats
                ->pluck('event_guest_id')
                ->filter()
                ->unique()
                ->values();


            /*
            |--------------------------------------------------------------------------
            | Unseated Members
            |--------------------------------------------------------------------------
            |
            | Order remains:
            |
            | 1. Primary
            | 2. Spouse
            |--------------------------------------------------------------------------
            */

            $unseatedMemberIds = $partyMemberIds
                ->filter(function ($partyMemberId) use ($seatedMemberIds) {

                    return !$seatedMemberIds
                        ->contains($partyMemberId);
                })
                ->values();


            /*
            |--------------------------------------------------------------------------
            | Unseated Guests
            |--------------------------------------------------------------------------
            */

            $unseatedGuests = $partyGuests
                ->filter(function ($guest) use ($seatedGuestIds) {

                    return !$seatedGuestIds
                        ->contains($guest->id);
                })
                ->values();


            /*
            |--------------------------------------------------------------------------
            | Number Of Seats Needed
            |--------------------------------------------------------------------------
            */

            $needsSeat =
                $unseatedMemberIds->count()
                +
                $unseatedGuests->count();


            /*
            |--------------------------------------------------------------------------
            | Everyone Already Seated
            |--------------------------------------------------------------------------
            */

            if ($needsSeat === 0) {

                DB::commit();

                return back()->with(
                    'success',
                    'Everyone in this party is already seated.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Available Seats At Selected Table
            |--------------------------------------------------------------------------
            */

            $availableSeats = $eventSeats
                ->filter(function ($seat) use ($tableId) {

                    return
                        (int) $seat->event_table_id === (int) $tableId
                        &&
                        $seat->seat_status === 'AVAILABLE'
                        &&
                        is_null($seat->member_id)
                        &&
                        is_null($seat->event_guest_id);
                })
                ->sortBy('seat_no')
                ->values();


            /*
            |--------------------------------------------------------------------------
            | Re-check Capacity
            |--------------------------------------------------------------------------
            */

            if ($availableSeats->count() < $needsSeat) {

                throw new \RuntimeException(
                    'The selected table no longer has enough available seats for this party.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Seat Pointer
            |--------------------------------------------------------------------------
            */

            $seatIndex = 0;


            /*
            |--------------------------------------------------------------------------
            | Assign Members
            |--------------------------------------------------------------------------
            */

            foreach ($unseatedMemberIds as $partyMemberId) {

                $seat = $availableSeats
                    ->get($seatIndex);


                if (!$seat) {

                    throw new \RuntimeException(
                        'Unable to locate an available seat for the party.'
                    );
                }


                DB::table('event_seats')
                    ->where('id', $seat->id)
                    ->update([

                        'member_id' =>
                        (int) $partyMemberId,

                        'event_guest_id' =>
                        null,

                        'seat_status' =>
                        'ASSIGNED',

                        'updated_by' =>
                        Auth::id(),

                        'updated_at' =>
                        now(),

                    ]);


                $seatIndex++;
            }


            /*
            |--------------------------------------------------------------------------
            | Assign Guests
            |--------------------------------------------------------------------------
            */

            foreach ($unseatedGuests as $guest) {

                $seat = $availableSeats
                    ->get($seatIndex);


                if (!$seat) {

                    throw new \RuntimeException(
                        'Unable to locate an available seat for the party.'
                    );
                }


                DB::table('event_seats')
                    ->where('id', $seat->id)
                    ->update([

                        'member_id' =>
                        null,

                        'event_guest_id' =>
                        (int) $guest->id,

                        'seat_status' =>
                        'ASSIGNED',

                        'updated_by' =>
                        Auth::id(),

                        'updated_at' =>
                        now(),

                    ]);


                $seatIndex++;
            }


            /*
            |--------------------------------------------------------------------------
            | Commit
            |--------------------------------------------------------------------------
            */

            DB::commit();


            return back()->with(
                'success',
                'Party seated successfully at ' .
                    ($selectedTable->table_name ?: 'Table ' . $selectedTable->table_no) .
                    '.'
            );
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);


            return back()->with(
                'error',
                $e instanceof \RuntimeException
                    ? $e->getMessage()
                    : 'Unable to seat the party. No seating changes were saved.'
            );
        }
    } // End Method


}
