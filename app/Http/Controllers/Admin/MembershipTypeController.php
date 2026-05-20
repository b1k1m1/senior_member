<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMembershipTypeRequest;
use App\Models\MembershipType;
use Illuminate\Http\Request;

class MembershipTypeController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.membership-types.index');
    }

    public function ajax(Request $request)
    {
        $query = MembershipType::select('membership_types.*');

        // Sorting
        $columns = ['name', 'fee_amount', 'is_active', 'created_at', 'actions'];
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
        
        $totalRecords = MembershipType::count();
        $filteredRecords = $query->count();
        
        $types = $query->skip($start)->take($length)->get();

        $data = $types->map(function ($type) {
            return [
                'id' => $type->id,
                'name' => $type->name,
                'fee_amount' => $type->fee_amount,
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
        return view('admin.membership-types.create');
    }

    public function store(StoreMembershipTypeRequest $request)
    {
        MembershipType::create($request->validated());

        return redirect()->route('admin.membership-types.index')
            ->with('success', 'Membership type created successfully.');
    }

    public function edit(MembershipType $membershipType)
    {
        return view('admin.membership-types.edit', compact('membershipType'));
    }

    public function update(Request $request, MembershipType $membershipType)
    {
        $request->validate([
            'name' => 'required|string|max:50|unique:membership_types,name,' . $membershipType->id,
            'fee_amount' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $membershipType->update($request->validated());

        return redirect()->route('admin.membership-types.index')
            ->with('success', 'Membership type updated successfully.');
    }

    public function destroy(MembershipType $membershipType)
    {
        if ($membershipType->members()->count() > 0) {
            return response()->json(['error' => 'Cannot delete membership type with associated members.'], 400);
        }

        $membershipType->delete();

        return response()->json(['success' => 'Membership type deleted successfully.']);
    }
}
