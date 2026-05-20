<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            MembershipTypeSeeder::class,
        ]);

        $user = User::firstOrCreate(
            ['email' => 'admin@membersystem.com'],
            [
                'name' => 'Super Admin',
                'username' => 'admin',
                'password' => bcrypt('password'),
                'plain_password' => 'password',
            ]
        );

        $role = Role::where('name', 'Super Admin')->first();
        if ($role && !$user->hasRole($role)) {
            $user->assignRole($role);
        }

        $this->call([
            MemberSeeder::class,
            PaymentSeeder::class,
        ]);
    }
}
