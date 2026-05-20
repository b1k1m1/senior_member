<?php

namespace App\Http\Controllers\Admin\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\Member;
use Illuminate\Http\Request;

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
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'status' => 'required|in:TENTATIVE,CONFIRMED,CANCELLED',
            'guests_count' => 'nullable|integer|min:0',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
            'receipt_no' => 'nullable|string|max:30',
            'remarks' => 'nullable|string',
        ]);

        // Check if already registered
        $existing = EventAttendee::where('event_id', $event->id)
            ->where('member_id', $request->member_id)
            ->first();

        if ($existing) {
            return back()->with('error', 'This member is already registered for this event.');
        }

        $data = $request->all();
        $data['event_id'] = $event->id;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        EventAttendee::create($data);

        return redirect()->route('admin.events.attendees.index', $event->id)
            ->with('success', 'Attendee added successfully.');
    }

    public function updateStatus(Request $request, EventAttendee $attendee)
    {
        $request->validate([
            'status' => 'required|in:TENTATIVE,CONFIRMED,CANCELLED',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_date' => 'nullable|date',
        ]);

        $attendee->update($request->all());

        return response()->json(['success' => 'Status updated successfully.']);
    }

    public function destroy(EventAttendee $attendee)
    {
        $attendee->delete();

        return response()->json(['success' => 'Attendee removed successfully.']);
    }
}
