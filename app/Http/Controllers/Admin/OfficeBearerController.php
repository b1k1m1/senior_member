<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OfficeBearer;
use Illuminate\Http\Request;

class OfficeBearerController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.office-bearers.index');
    }

    public function ajax(Request $request)
    {
        $query = OfficeBearer::select('office_bearers.*');

        $columns = ['position', 'name', 'phone', 'display_order', 'actions'];
        if ($request->has('order') && count($request->order) > 0) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'asc';
            $orderColumn = $columns[$orderColumnIndex] ?? 'display_order';
            $query->orderBy($orderColumn, $orderDir);
        } else {
            $query->orderBy('display_order');
        }

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        
        $totalRecords = OfficeBearer::count();
        $filteredRecords = $query->count();
        
        $bearers = $query->skip($start)->take($length)->get();

        $data = $bearers->map(function ($bearer) {
            return [
                'id' => $bearer->id,
                'position' => $bearer->position,
                'name' => $bearer->name,
                'phone' => $bearer->phone,
                'display_order' => $bearer->display_order,
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
        return view('admin.office-bearers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'position' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'display_order' => 'nullable|integer',
        ]);

        OfficeBearer::create($request->all());

        return redirect()->route('admin.office-bearers.index')
            ->with('success', 'Office bearer created successfully.');
    }

    public function edit(OfficeBearer $officeBearer)
    {
        return view('admin.office-bearers.edit', compact('officeBearer'));
    }

    public function update(Request $request, OfficeBearer $officeBearer)
    {
        $request->validate([
            'position' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'display_order' => 'nullable|integer',
        ]);

        $officeBearer->update($request->all());

        return redirect()->route('admin.office-bearers.index')
            ->with('success', 'Office bearer updated successfully.');
    }

    public function destroy(OfficeBearer $officeBearer)
    {
        $officeBearer->delete();
        return response()->json(['success' => 'Office bearer deleted successfully.']);
    }
}
