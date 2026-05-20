<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'members.menu',
            'members.view',
            'members.create',
            'members.edit',
            'members.delete',
            'membership_types.manage',
            'payments.manage',
            'reports.view',
            'import.export',
            'events.menu',
            'events.view',
            'events.create',
            'events.edit',
            'events.delete',
            'event_types.manage',
            'system.admin',
            'receipts.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->givePermissionTo([
            'members.menu',
            'members.view',
            'members.create',
            'members.edit',
            'membership_types.manage',
            'payments.manage',
            'reports.view',
            'import.export',
            'events.menu',
            'events.view',
            'events.create',
            'events.edit',
            'event_types.manage',
        ]);

        $clerk = Role::firstOrCreate(['name' => 'Clerk', 'guard_name' => 'web']);
        $clerk->givePermissionTo([
            'members.menu',
            'members.view',
            'members.create',
            'members.edit',
            'payments.manage',
            'reports.view',
            'events.menu',
            'events.view',
        ]);

        $viewer = Role::firstOrCreate(['name' => 'Viewer', 'guard_name' => 'web']);
        $viewer->givePermissionTo([
            'members.menu',
            'members.view',
            'reports.view',
        ]);
    }
}
