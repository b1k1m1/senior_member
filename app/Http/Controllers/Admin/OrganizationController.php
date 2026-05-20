<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.organizations.index');
    }

    public function ajax(Request $request)
    {
        $query = Organization::select('organizations.*');

        $columns = ['name', 'phone', 'email', 'actions'];
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
        
        $totalRecords = Organization::count();
        $filteredRecords = $query->count();
        
        $organizations = $query->skip($start)->take($length)->get();

        $data = $organizations->map(function ($org) {
            return [
                'id' => $org->id,
                'name' => $org->name,
                'phone' => $org->phone,
                'email' => $org->email,
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
        return view('admin.organizations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'website' => 'nullable|string',
            'tax_id' => 'nullable|string|max:50',
            'registration_no' => 'nullable|string|max:50',
            'founder_name' => 'nullable|string|max:255',
            'founder_title' => 'nullable|string|max:255',
            'welcome_message' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->hasFile('founder_photo')) {
            $path = $request->file('founder_photo')->store('organizations', 'public');
            $data['founder_photo'] = $path;
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('organizations', 'public');
            $data['logo'] = $path;
        }

        Organization::create($data);

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization created successfully.');
    }

    public function edit(Organization $organization)
    {
        return view('admin.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'website' => 'nullable|string',
            'tax_id' => 'nullable|string|max:50',
            'registration_no' => 'nullable|string|max:50',
            'founder_name' => 'nullable|string|max:255',
            'founder_title' => 'nullable|string|max:255',
            'welcome_message' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->hasFile('founder_photo')) {
            if ($organization->founder_photo) {
                File::delete('storage/' . $organization->founder_photo);
            }
            $path = $request->file('founder_photo')->store('organizations', 'public');
            $data['founder_photo'] = $path;
        }

        if ($request->hasFile('logo')) {
            if ($organization->logo) {
                File::delete('storage/' . $organization->logo);
            }
            $path = $request->file('logo')->store('organizations', 'public');
            $data['logo'] = $path;
        }

        $organization->update($data);

        return redirect()->route('admin.organizations.index')
            ->with('success', 'Organization updated successfully.');
    }

    public function destroy(Organization $organization)
    {
        if ($organization->founder_photo) {
            File::delete('storage/' . $organization->founder_photo);
        }
        if ($organization->logo) {
            File::delete('storage/' . $organization->logo);
        }
        
        $organization->delete();
        return response()->json(['success' => 'Organization deleted successfully.']);
    }
}
