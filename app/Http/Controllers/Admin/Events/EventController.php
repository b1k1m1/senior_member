<?php

namespace App\Http\Controllers\Admin\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.events.index');
    }

    public function ajax(Request $request)
    {
        $query = Event::with('eventType')->select('events.*');

        $columns = ['title', 'event_type_id', 'start_date', 'end_date', 'status', 'capacity', 'actions'];
        if ($request->has('order') && count($request->order) > 0) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'asc';
            $orderColumn = $columns[$orderColumnIndex] ?? 'start_date';
            
            if ($orderColumn === 'event_type_id') {
                $query->join('event_types', 'events.event_type_id', '=', 'event_types.id')
                      ->orderBy('event_types.name', $orderDir);
            } else {
                $query->orderBy($orderColumn, $orderDir);
            }
        } else {
            $query->orderBy('start_date', 'desc');
        }

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        
        $totalRecords = Event::count();
        $filteredRecords = $query->count();
        
        $events = $query->skip($start)->take($length)->get();

        $data = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'event_type' => $event->eventType?->name,
                'start_date' => $event->start_date->format('Y-m-d'),
                'end_date' => $event->end_date->format('Y-m-d'),
                'status' => $event->status,
                'capacity' => $event->capacity,
                'confirmed' => $event->confirmed_count,
                'tentative' => $event->tentative_count,
            ];
        });

        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function create()
    {
        $eventTypes = EventType::where('is_active', true)->orderBy('name')->get();
        return view('admin.events.create', compact('eventTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'event_type_id' => 'required|exists:event_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:ACTIVE,SCHEDULED,RESCHEDULED,CANCELLED,COMPLETED',
            'confirmation_deadline' => 'nullable|date',
            'min_attendees' => 'nullable|integer|min:0',
            'attendance_type' => 'required|in:MEMBERS_ONLY,MEMBERS_WITH_GUESTS',
            'max_guests_per_member' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        Event::create($data);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event created successfully.');
    }

    public function show(Event $event)
    {
        $event->load(['eventType', 'attendees.member']);
        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $eventTypes = EventType::where('is_active', true)->orderBy('name')->get();
        return view('admin.events.edit', compact('event', 'eventTypes'));
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'event_type_id' => 'required|exists:event_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:ACTIVE,SCHEDULED,RESCHEDULED,CANCELLED,COMPLETED',
            'confirmation_deadline' => 'nullable|date',
            'min_attendees' => 'nullable|integer|min:0',
            'attendance_type' => 'required|in:MEMBERS_ONLY,MEMBERS_WITH_GUESTS',
            'max_guests_per_member' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['updated_by'] = auth()->id();

        $event->update($data);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event updated successfully.');
    }

    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json(['success' => 'Event deleted successfully.']);
    }
}
