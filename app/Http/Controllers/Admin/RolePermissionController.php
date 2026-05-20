<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    public function index(Request $request, Role $role = null)
    {
        $roles = Role::all();
        $allPermissions = Permission::orderBy('name')->get();
        
        // Check if role is passed as route parameter or query parameter
        $selectedRoleId = $role ? $role->id : $request->get('role');
        $selectedRole = null;
        
        if ($selectedRoleId) {
            $selectedRole = Role::with('permissions')->find($selectedRoleId);
        }
        
        return view('admin.roles.permissions', compact('roles', 'allPermissions', 'selectedRole'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()->route('admin.roles.permissions.index', ['role' => $role->id])
            ->with('success', 'Permissions updated for role: ' . $role->name);
    }
}
