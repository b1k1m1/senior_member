<?php

namespace App\Http\Controllers\Admin\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EventAttendeeController extends Controller
{
    public function index(Request $request, Event $event)
    {
        $event->load('attendees.member');
        return view('admin.event-attendees.index', compact('event'));
    }

    public function ajax(Request $request, Event $event)
    {
        $query = $event->attendees()->with('member')->select('event_attendees.*');

        $columns = ['member_id', 'status', 'guests_count', 'amount_paid', 'payment_date', 'actions'];
        if ($request->has('order') && count($request->order) > 0) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'asc';
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';
            $query->orderBy($orderColumn, $orderDir);
        } else {
            $query->orderBy('id', 'desc');
        }

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;

        $totalRecords = $event->attendees()->count();
        $filteredRecords = $query->count();

        $attendees = $query->skip($start)->take($length)->get();

        $data = $attendees->map(function ($attendee) {
            return [
                'id' => $attendee->id,
                'member_name' => $attendee->member->full_name,
                'member_no' => $attendee->member->member_no,
                'status' => $attendee->status,
                'guests_count' => $attendee->guests_count,
                'amount_paid' => $attendee->amount_paid,
                'payment_date' => $attendee->payment_date?->format('Y-m-d'),
            ];
        });

        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function create(Event $event)
    {
        return view('admin.event-attendees.create', compact('event'));
    }

    public function store(Request $request, Event $event)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'member_id' => [
                'required',
                'exists:members,id',
            ],

            'status' => [
                'required',
                'in:TENTATIVE,CONFIRMED,CANCELLED',
            ],

            'spouse_attending' => [
                'nullable',
                'boolean',
            ],

            'spouse_member_id' => [
                'nullable',
                'integer',
                'exists:members,id',
            ],

            'guests_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:' . ($event->max_guests_per_member ?? 0),
            ],

            /*
            |--------------------------------------------------------------------------
            | Main / Combined Payment
            |--------------------------------------------------------------------------
            */

            'amount_paid' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Spouse Payment - Used Only For SEPARATE Mode
            |--------------------------------------------------------------------------
            */

            'spouse_amount_paid' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'payment_date' => [
                'nullable',
                'date',
            ],

            'receipt_no' => [
                'nullable',
                'string',
                'max:30',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Main Member
        |--------------------------------------------------------------------------
        */

        $member = Member::findOrFail(
            $validated['member_id']
        );


        /*
        |--------------------------------------------------------------------------
        | Already Registered?
        |--------------------------------------------------------------------------
        */

        $existing = EventAttendee::where(
            'event_id',
            $event->id
        )
            ->where(
                'member_id',
                $member->id
            )
            ->first();


        if ($existing) {

            return back()
                ->with(
                    'error',
                    'This member is already registered for this event.'
                )
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Spouse Attending?
        |--------------------------------------------------------------------------
        */

        $spouseAttending =
            $request->boolean('spouse_attending');


        $spouse = null;


        /*
        |--------------------------------------------------------------------------
        | Verify Spouse Server-Side
        |--------------------------------------------------------------------------
        |
        | Do NOT trust spouse_member_id from the browser by itself.
        | The spouse must have the same membership receipt_no.
        |--------------------------------------------------------------------------
        */

        if ($spouseAttending) {

            if (empty($member->receipt_no)) {

                return back()
                    ->with(
                        'error',
                        'Unable to verify the associated spouse for this member.'
                    )
                    ->withInput();
            }


            $spouseQuery = Member::where(
                'receipt_no',
                $member->receipt_no
            )
                ->where(
                    'id',
                    '!=',
                    $member->id
                )
                ->where(
                    'status',
                    'ACTIVE'
                );


            /*
            |--------------------------------------------------------------------------
            | Match Browser Selection Too
            |--------------------------------------------------------------------------
            */

            if (!empty($validated['spouse_member_id'])) {

                $spouseQuery->where(
                    'id',
                    $validated['spouse_member_id']
                );
            }


            $spouse = $spouseQuery->first();


            if (!$spouse) {

                return back()
                    ->with(
                        'error',
                        'Associated spouse could not be verified.'
                    )
                    ->withInput();
            }


            /*
            |--------------------------------------------------------------------------
            | Is Spouse Already Registered?
            |--------------------------------------------------------------------------
            */

            $spouseExisting = EventAttendee::where(
                'event_id',
                $event->id
            )
                ->where(
                    'member_id',
                    $spouse->id
                )
                ->exists();


            if ($spouseExisting) {

                return back()
                    ->with(
                        'error',
                        'The associated spouse is already registered for this event.'
                    )
                    ->withInput();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Payment Amounts
        |--------------------------------------------------------------------------
        */

        $mainAmountPaid =
            (float) ($validated['amount_paid'] ?? 0);


        $spouseAmountPaid =
            (float) ($validated['spouse_amount_paid'] ?? 0);


        /*
        |--------------------------------------------------------------------------
        | Confirmation Payment Rules
        |--------------------------------------------------------------------------
        */

        if ($validated['status'] === 'CONFIRMED') {

            /*
            |--------------------------------------------------------------------------
            | No Spouse
            |--------------------------------------------------------------------------
            */

            if (!$spouseAttending) {

                if ($mainAmountPaid <= 0) {

                    return back()
                        ->withErrors([
                            'amount_paid' =>
                            'Payment is required before attendee can be confirmed.',
                        ])
                        ->withInput();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Spouse Attending - Separate Charge
            |--------------------------------------------------------------------------
            */ elseif (
                $event->spouse_charge_mode === 'SEPARATE'
            ) {

                if ($mainAmountPaid <= 0) {

                    return back()
                        ->withErrors([
                            'amount_paid' =>
                            'Primary member payment is required before confirmation.',
                        ])
                        ->withInput();
                }


                if ($spouseAmountPaid <= 0) {

                    return back()
                        ->withErrors([
                            'spouse_amount_paid' =>
                            'Spouse payment is required before confirmation.',
                        ])
                        ->withInput();
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Spouse Attending - Combined Charge
            |--------------------------------------------------------------------------
            */ elseif (
                $event->spouse_charge_mode === 'COMBINED'
            ) {

                if ($mainAmountPaid <= 0) {

                    return back()
                        ->withErrors([
                            'amount_paid' =>
                            'Combined couple payment is required before confirmation.',
                        ])
                        ->withInput();
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Payment Date Required When Any Payment Exists
        |--------------------------------------------------------------------------
        */

        $paymentEntered =
            $mainAmountPaid > 0 ||
            $spouseAmountPaid > 0;


        if (
            $paymentEntered &&
            empty($validated['payment_date'])
        ) {

            return back()
                ->withErrors([
                    'payment_date' =>
                    'Payment date is required when payment is entered.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Save In Transaction
        |--------------------------------------------------------------------------
        */

        DB::beginTransaction();


        try {

            /*
            |--------------------------------------------------------------------------
            | Main Member Attendee
            |--------------------------------------------------------------------------
            */

            EventAttendee::create([

                'event_id' =>
                $event->id,

                'member_id' =>
                $member->id,

                'status' =>
                $validated['status'],

                'guests_count' =>
                (int) ($validated['guests_count'] ?? 0),

                'amount_paid' =>
                $mainAmountPaid,

                'payment_date' =>
                $validated['payment_date'] ?? null,

                'receipt_no' =>
                $validated['receipt_no'] ?? null,

                'remarks' =>
                $validated['remarks'] ?? null,

                'created_by' =>
                Auth::id(),

                'updated_by' =>
                Auth::id(),
            ]);


            /*
            |--------------------------------------------------------------------------
            | Create Spouse Attendee
            |--------------------------------------------------------------------------
            */

            if (
                $spouseAttending &&
                $spouse
            ) {

                /*
                |--------------------------------------------------------------------------
                | Separate = spouse gets their own payment
                | Combined = spouse amount is zero because payment is on main record
                |--------------------------------------------------------------------------
                */

                $spousePayment =
                    $event->spouse_charge_mode === 'SEPARATE'
                    ? $spouseAmountPaid
                    : 0;


                EventAttendee::create([

                    'event_id' =>
                    $event->id,

                    'member_id' =>
                    $spouse->id,

                    'status' =>
                    $validated['status'],

                    /*
                    |--------------------------------------------------------------------------
                    | Guests Belong To Selected Main Attendee
                    |--------------------------------------------------------------------------
                    */

                    'guests_count' =>
                    0,

                    'amount_paid' =>
                    $spousePayment,

                    /*
                    |--------------------------------------------------------------------------
                    | Same Payment Date / Receipt No
                    |--------------------------------------------------------------------------
                    */

                    'payment_date' =>
                    $validated['payment_date'] ?? null,

                    'receipt_no' =>
                    $validated['receipt_no'] ?? null,

                    'remarks' =>
                    $validated['remarks'] ?? null,

                    'created_by' =>
                    Auth::id(),

                    'updated_by' =>
                    Auth::id(),
                ]);
            }


            DB::commit();
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);


            return back()
                ->with(
                    'error',
                    'Unable to add attendee. Please try again.'
                )
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Success
        |--------------------------------------------------------------------------
        */

        $message =
            $spouseAttending
            ? 'Member and spouse added successfully.'
            : 'Attendee added successfully.';


        return redirect()
            ->route(
                'admin.event-attendees.index',
                $event->id
            )
            ->with(
                'success',
                $message
            );
    } // End Method

    public function updateStatus(Request $request, Event $event, EventAttendee $attendee)
    {
        /*
        |--------------------------------------------------------------------------
        | Make Sure Attendee Belongs To Event
        |--------------------------------------------------------------------------
        */

        if ((int) $attendee->event_id !== (int) $event->id) {

            return response()->json([
                'success' => false,
                'message' => 'Attendee does not belong to this event.',
            ], 404);
        }


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'status' => [
                'required',
                'in:TENTATIVE,CONFIRMED,CANCELLED',
            ],

            'amount_paid' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'payment_date' => [
                'nullable',
                'date',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Update Data
        |--------------------------------------------------------------------------
        */

        $updateData = [
            'status' => $validated['status'],

            'updated_by' => Auth::id(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Only Update Payment Fields If They Were Actually Sent
        |--------------------------------------------------------------------------
        */

        if ($request->has('amount_paid')) {

            $updateData['amount_paid'] =
                $validated['amount_paid'] ?? 0;
        }


        if ($request->has('payment_date')) {

            $updateData['payment_date'] =
                $validated['payment_date'] ?? null;
        }


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $attendee->update(
            $updateData
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'success' => true,
            'message' => 'Attendee status updated successfully.',
            'status' => $attendee->status,
        ]);
    }

    public function edit(Event $event, EventAttendee $attendee)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Attendee Belongs To Event
        |--------------------------------------------------------------------------
        */

        if ((int) $attendee->event_id !== (int) $event->id) {
            abort(404, 'Attendee not found for this event.');
        }


        /*
        |--------------------------------------------------------------------------
        | Load Member
        |--------------------------------------------------------------------------
        */

        $attendee->load('member');


        return view(
            'admin.event-attendees.edit',
            compact(
                'event',
                'attendee'
            )
        );
    }

    public function update(Request $request, Event $event, EventAttendee $attendee)
    {
        /*
        |--------------------------------------------------------------------------
        | Validate Attendee Belongs To Event
        |--------------------------------------------------------------------------
        */

        if ((int) $attendee->event_id !== (int) $event->id) {
            abort(404, 'Attendee not found for this event.');
        }


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'status' => [
                'required',
                'in:TENTATIVE,CONFIRMED,CANCELLED',
            ],

            'guests_count' => [
                'nullable',
                'integer',
                'min:0',
                'max:' . ($event->max_guests_per_member ?? 0),
            ],

            'amount_paid' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'payment_date' => [
                'nullable',
                'date',
            ],

            'receipt_no' => [
                'nullable',
                'string',
                'max:30',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Values
        |--------------------------------------------------------------------------
        */

        $amountPaid =
            (float) ($validated['amount_paid'] ?? 0);

        $eventReceiptNo =
            trim($validated['receipt_no'] ?? '');


        /*
        |--------------------------------------------------------------------------
        | Payment Rule For Confirmation
        |--------------------------------------------------------------------------
        */

        if ($validated['status'] === 'CONFIRMED') {

            /*
            |--------------------------------------------------------------------------
            | Attendee Has Own Payment
            |--------------------------------------------------------------------------
            */

            if ($amountPaid > 0) {

                // Valid - attendee has their own payment.

            }

            /*
            |--------------------------------------------------------------------------
            | No Payment - Only Allowed For COMBINED Spouse Registration
            |--------------------------------------------------------------------------
            */ else {

                $coveredByCombinedPayment = false;


                /*
                |--------------------------------------------------------------------------
                | Event Must Be COMBINED
                |--------------------------------------------------------------------------
                */

                if (
                    $event->spouse_charge_mode === 'COMBINED'
                    && $eventReceiptNo !== ''
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Current Member
                    |--------------------------------------------------------------------------
                    */

                    $member = Member::find(
                        $attendee->member_id
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Find Associated Member Using Membership Receipt Number
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $member
                        && !empty($member->receipt_no)
                    ) {

                        $associatedMember = Member::where(
                            'receipt_no',
                            $member->receipt_no
                        )
                            ->where(
                                'id',
                                '!=',
                                $member->id
                            )
                            ->where(
                                'status',
                                'ACTIVE'
                            )
                            ->first();


                        /*
                        |--------------------------------------------------------------------------
                        | Find Associated Event Attendee
                        |--------------------------------------------------------------------------
                        */

                        if ($associatedMember) {

                            $payingAttendee =
                                EventAttendee::where(
                                    'event_id',
                                    $event->id
                                )
                                ->where(
                                    'member_id',
                                    $associatedMember->id
                                )
                                ->where(
                                    'receipt_no',
                                    $eventReceiptNo
                                )
                                ->where(
                                    'status',
                                    'CONFIRMED'
                                )
                                ->where(
                                    'amount_paid',
                                    '>',
                                    0
                                )
                                ->first();


                            if ($payingAttendee) {

                                $coveredByCombinedPayment = true;
                            }
                        }
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Not Covered
                |--------------------------------------------------------------------------
                */

                if (!$coveredByCombinedPayment) {

                    return back()
                        ->withErrors([
                            'status' =>
                            'Payment is required before attendee can be confirmed.',
                        ])
                        ->withInput();
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Payment Date Rule
        |--------------------------------------------------------------------------
        */

        if (
            $amountPaid > 0
            && empty($validated['payment_date'])
        ) {

            return back()
                ->withErrors([
                    'payment_date' =>
                    'Payment date is required when payment amount is entered.',
                ])
                ->withInput();
        }


        /*
        |--------------------------------------------------------------------------
        | Update Attendee
        |--------------------------------------------------------------------------
        */

        $attendee->update([
            'status' =>
            $validated['status'],

            'guests_count' =>
            (int) ($validated['guests_count'] ?? 0),

            'amount_paid' =>
            $amountPaid,

            'payment_date' =>
            $validated['payment_date'] ?? null,

            'receipt_no' =>
            $validated['receipt_no'] ?? null,

            'remarks' =>
            $validated['remarks'] ?? null,

            'updated_by' =>
            Auth::id(),
        ]);


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'admin.event-attendees.index',
                $event->id
            )
            ->with(
                'success',
                'Attendee updated successfully.'
            );
    } // End Method

    public function destroy(EventAttendee $attendee)
    {
        $attendee->delete();

        return response()->json(['success' => 'Attendee removed successfully.']);
    }

    public function findSpouse(Event $event, Member $member)
    {
        /*
        |--------------------------------------------------------------------------
        | Member Must Have Membership Receipt Number
        |--------------------------------------------------------------------------
        */

        if (
            empty($member->receipt_no)
        ) {
            return response()->json([
                'found' => false,
                'message' => 'No associated spouse found.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Find Another Active Member With Same Membership Receipt Number
        |--------------------------------------------------------------------------
        */

        $spouse = Member::where(
            'receipt_no',
            $member->receipt_no
        )
            ->where(
                'id',
                '!=',
                $member->id
            )
            ->where(
                'status',
                'ACTIVE'
            )
            ->orderBy('id')
            ->first();


        /*
        |--------------------------------------------------------------------------
        | No Spouse Found
        |--------------------------------------------------------------------------
        */

        if (!$spouse) {

            return response()->json([
                'found' => false,
                'message' => 'No associated spouse found.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Check Whether Spouse Is Already Registered For Event
        |--------------------------------------------------------------------------
        */

        $alreadyRegistered = EventAttendee::where(
            'event_id',
            $event->id
        )
            ->where(
                'member_id',
                $spouse->id
            )
            ->exists();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return response()->json([
            'found' => true,

            'spouse' => [
                'id' => $spouse->id,

                'member_no' =>
                $spouse->member_no,

                'first_name' =>
                $spouse->first_name,

                'last_name' =>
                $spouse->last_name,

                'full_name' =>
                trim(
                    $spouse->first_name .
                        ' ' .
                        $spouse->last_name
                ),

                'already_registered' =>
                $alreadyRegistered,
            ],
        ]);
    } // End Method

    public function guests(Event $event, EventAttendee $attendee)
    {
        if ((int) $attendee->event_id !== (int) $event->id) {
            abort(404, 'Attendee not found for this event.');
        }

        $attendee->load('member');

        $guests = DB::table('event_attendee_guests')
            ->where('event_attendee_id', $attendee->id)
            ->orderBy('guest_no')
            ->get();

        return view(
            'admin.event-attendees.guests',
            compact(
                'event',
                'attendee',
                'guests'
            )
        );
    } // End Method

    public function storeGuests(Request $request, Event $event, EventAttendee $attendee)
    {
        if ((int) $attendee->event_id !== (int) $event->id) {
            abort(404, 'Attendee not found for this event.');
        }

        $maxGuests = (int) $attendee->guests_count;

        if ($maxGuests <= 0) {
            return back()->with(
                'error',
                'This attendee does not have any guests assigned.'
            );
        }

        $validated = $request->validate([
            'guests' => [
                'required',
                'array',
                'max:' . $maxGuests,
            ],

            'guests.*.guest_name' => [
                'required',
                'string',
                'max:120',
            ],

            'guests.*.guest_type' => [
                'required',
                'in:FAMILY,GUEST',
            ],

            'guests.*.remarks' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        DB::beginTransaction();

        try {

            DB::table('event_attendee_guests')
                ->where('event_attendee_id', $attendee->id)
                ->delete();

            $guestNo = 1;

            foreach ($validated['guests'] as $guest) {

                DB::table('event_attendee_guests')
                    ->insert([
                        'event_attendee_id' => $attendee->id,
                        'guest_no' => $guestNo,
                        'guest_name' => trim($guest['guest_name']),
                        'guest_type' => $guest['guest_type'],
                        'remarks' => $guest['remarks'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                $guestNo++;
            }

            DB::commit();

            return redirect()
                ->route(
                    'admin.event-attendees.index',
                    $event->id
                )
                ->with(
                    'success',
                    'Guest information saved successfully.'
                );
        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return back()
                ->with(
                    'error',
                    'Unable to save guest information.'
                )
                ->withInput();
        }
    } // End Method

}
