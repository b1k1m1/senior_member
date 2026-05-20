<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\Payment;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::all();
        $methods = ['CASH', 'CHECK', 'CARD', 'OTHER'];

        $paymentCount = 0;
        
        foreach ($members as $member) {
            $numPayments = rand(1, 3);
            
            for ($i = 0; $i < $numPayments; $i++) {
                $paymentDate = now()->subMonths(rand(0, 12));
                
                Payment::firstOrCreate(
                    [
                        'member_id' => $member->id,
                        'payment_date' => $paymentDate->format('Y-m-d'),
                        'amount' => rand(50, 500),
                    ],
                    [
                        'method' => $methods[array_rand($methods)],
                        'receipt_no' => 'RCP' . str_pad(($paymentCount + 1), 5, '0', STR_PAD_LEFT),
                        'remarks' => 'Annual membership fee',
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]
                );
                
                $paymentCount++;
            }
        }
    }
}
