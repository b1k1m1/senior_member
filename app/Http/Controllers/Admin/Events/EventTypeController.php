<?php

namespace App\Http\Controllers\Admin\Events;

use App\Http\Controllers\Controller;
use App\Models\EventType;
use Illuminate\Http\Request;

class EventTypeController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.event-types.index');
    }

    public function ajax(Request $request)
    {
        $query = EventType::select('event_types.*');

        $columns = ['name', 'is_active', 'created_at', 'actions'];
        if ($request->has('order') && count($request->order) > 0) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'asc';
            $orderColumn = $columns[$orderColumnIndex] ?? 'name';
            $query->orderBy($orderColumn, $orderDir);
        } else {
            $query->orderBy('name');
        }

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        
        $totalRecords = EventType::count();
        $filteredRecords = $query->count();
        
        $types = $query->skip($start)->take($length)->get();

        $data = $types->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'is_active' => $type->is_active,
                'created_at' => $type->created_at->format('Y-m-d'),
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
        return view('admin.event-types.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:event_types,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        EventType::create($request->all());

        return redirect()->route('admin.event-types.index')
            ->with('success', 'Event type created successfully.');
    }

    public function edit(EventType $eventType)
    {
        return view('admin.event-types.edit', compact('eventType'));
    }

    public function update(Request $request, EventType $eventType)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:event_types,name,' . $eventType->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $eventType->update($request->all());

        return redirect()->route('admin.event-types.index')
            ->with('success', 'Event type updated successfully.');
    }

    public function destroy(EventType $eventType)
    {
        if ($eventType->events()->count() > 0) {
            return response()->json(['error' => 'Cannot delete event type with associated events.'], 400);
        }

        $eventType->delete();

        return response()->json(['success' => 'Event type deleted successfully.']);
    }
}
