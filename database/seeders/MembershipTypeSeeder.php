<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MembershipType;

class MembershipTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'LIFE', 'fee_amount' => 500.00, 'is_active' => true],
            ['name' => 'ANNUAL', 'fee_amount' => 100.00, 'is_active' => true],
            ['name' => 'SENIOR', 'fee_amount' => 50.00, 'is_active' => true],
        ];

        foreach ($types as $type) {
            MembershipType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
