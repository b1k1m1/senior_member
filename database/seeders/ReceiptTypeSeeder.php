<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReceiptType;

class ReceiptTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Life Membership', 'code' => 'MEMBERSHIP', 'description' => 'New Life Membership Application'],
            ['name' => 'Membership Renewal', 'code' => 'RENEWAL', 'description' => 'Membership Renewal'],
            ['name' => 'Event Registration', 'code' => 'EVENT', 'description' => 'Event Registration Fee'],
            ['name' => 'Donation', 'code' => 'DONATION', 'description' => 'General Donation'],
            ['name' => 'Fund Raising', 'code' => 'FUND_RAISING', 'description' => 'Fund Raising Contribution'],
            ['name' => 'Other', 'code' => 'OTHER', 'description' => 'Other Payments'],
        ];

        foreach ($types as $type) {
            ReceiptType::firstOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
