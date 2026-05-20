<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use App\Models\MembershipType;
use Illuminate\Support\Facades\DB;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $types = MembershipType::all();
        
        $members = [
            ['member_no' => 'L0001', 'first_name' => 'JOHN', 'last_name' => 'SMITH', 'email' => 'john.smith@email.com', 'phone' => '555-1234', 'city' => 'New York', 'state' => 'NY', 'zip' => '10001', 'status' => 'ACTIVE'],
            ['member_no' => 'L0002', 'first_name' => 'JANE', 'last_name' => 'DOE', 'email' => 'jane.doe@email.com', 'phone' => '555-2345', 'city' => 'Los Angeles', 'state' => 'CA', 'zip' => '90001', 'status' => 'ACTIVE'],
            ['member_no' => 'L0003', 'first_name' => 'ROBERT', 'last_name' => 'JOHNSON', 'email' => 'r.johnson@email.com', 'phone' => '555-3456', 'city' => 'Chicago', 'state' => 'IL', 'zip' => '60601', 'status' => 'ACTIVE'],
            ['member_no' => 'L0004', 'first_name' => 'MARY', 'last_name' => 'WILLIAMS', 'email' => 'mary.w@email.com', 'phone' => '555-4567', 'city' => 'Houston', 'state' => 'TX', 'zip' => '77001', 'status' => 'ACTIVE'],
            ['member_no' => 'L0005', 'first_name' => 'JAMES', 'last_name' => 'BROWN', 'email' => 'j.brown@email.com', 'phone' => '555-5678', 'city' => 'Phoenix', 'state' => 'AZ', 'zip' => '85001', 'status' => 'ACTIVE'],
            ['member_no' => 'L0006', 'first_name' => 'PATRICIA', 'last_name' => 'GARcia', 'email' => 'p.garcia@email.com', 'phone' => '555-6789', 'city' => 'Philadelphia', 'state' => 'PA', 'zip' => '19101', 'status' => 'ACTIVE'],
            ['member_no' => 'L0007', 'first_name' => 'MICHAEL', 'last_name' => 'MILLER', 'email' => 'm.miller@email.com', 'phone' => '555-7890', 'city' => 'San Antonio', 'state' => 'TX', 'zip' => '78201', 'status' => 'ACTIVE'],
            ['member_no' => 'L0008', 'first_name' => 'LINDA', 'last_name' => 'DAVIS', 'email' => 'l.davis@email.com', 'phone' => '555-8901', 'city' => 'San Diego', 'state' => 'CA', 'zip' => '92101', 'status' => 'ACTIVE'],
            ['member_no' => 'L0009', 'first_name' => 'DAVID', 'last_name' => 'RODRIGUEZ', 'email' => 'd.rodriguez@email.com', 'phone' => '555-9012', 'city' => 'Dallas', 'state' => 'TX', 'zip' => '75201', 'status' => 'ACTIVE'],
            ['member_no' => 'L0010', 'first_name' => 'BARBARA', 'last_name' => 'MARTINEZ', 'email' => 'b.martinez@email.com', 'phone' => '555-0123', 'city' => 'San Jose', 'state' => 'CA', 'zip' => '95101', 'status' => 'ACTIVE'],
            ['member_no' => 'L0011', 'first_name' => 'WILLIAM', 'last_name' => 'ANDERSON', 'email' => 'w.anderson@email.com', 'phone' => '555-1111', 'city' => 'Austin', 'state' => 'TX', 'zip' => '78701', 'status' => 'ACTIVE'],
            ['member_no' => 'L0012', 'first_name' => 'ELIZABETH', 'last_name' => 'THOMAS', 'email' => 'e.thomas@email.com', 'phone' => '555-2222', 'city' => 'Jacksonville', 'state' => 'FL', 'zip' => '32201', 'status' => 'ACTIVE'],
            ['member_no' => 'L0013', 'first_name' => 'RICHARD', 'last_name' => 'TAYLOR', 'email' => 'r.taylor@email.com', 'phone' => '555-3333', 'city' => 'Fort Worth', 'state' => 'TX', 'zip' => '76101', 'status' => 'INACTIVE'],
            ['member_no' => 'L0014', 'first_name' => 'SUSAN', 'last_name' => 'MOORE', 'email' => 's.moore@email.com', 'phone' => '555-4444', 'city' => 'Columbus', 'state' => 'OH', 'zip' => '43201', 'status' => 'ACTIVE'],
            ['member_no' => 'L0015', 'first_name' => 'JOSEPH', 'last_name' => 'JACKSON', 'email' => 'j.jackson@email.com', 'phone' => '555-5555', 'city' => 'Charlotte', 'state' => 'NC', 'zip' => '28201', 'status' => 'ACTIVE'],
            ['member_no' => 'L0016', 'first_name' => 'JESSICA', 'last_name' => 'WHITE', 'email' => 'j.white@email.com', 'phone' => '555-6666', 'city' => 'San Francisco', 'state' => 'CA', 'zip' => '94101', 'status' => 'ACTIVE'],
            ['member_no' => 'L0017', 'first_name' => 'THOMAS', 'last_name' => 'HARRIS', 'email' => 't.harris@email.com', 'phone' => '555-7777', 'city' => 'Indianapolis', 'state' => 'IN', 'zip' => '46201', 'status' => 'ACTIVE'],
            ['member_no' => 'L0018', 'first_name' => 'SARAH', 'last_name' => 'MARTIN', 'email' => 's.martin@email.com', 'phone' => '555-8888', 'city' => 'Seattle', 'state' => 'WA', 'zip' => '98101', 'status' => 'INACTIVE'],
            ['member_no' => 'L0019', 'first_name' => 'CHARLES', 'last_name' => 'THOMPSON', 'email' => 'c.thompson@email.com', 'phone' => '555-9999', 'city' => 'Denver', 'state' => 'CO', 'zip' => '80201', 'status' => 'ACTIVE'],
            ['member_no' => 'L0020', 'first_name' => 'KAREN', 'last_name' => 'GARCIA', 'email' => 'k.garcia@email.com', 'phone' => '555-1010', 'city' => 'Washington', 'state' => 'DC', 'zip' => '20001', 'status' => 'ACTIVE'],
        ];

        foreach ($members as $index => $memberData) {
            $typeIndex = $index % $types->count();
            $startDate = now()->subMonths(rand(1, 36));
            
            Member::firstOrCreate(
                ['member_no' => $memberData['member_no']],
                array_merge($memberData, [
                    'membership_type_id' => $types[$typeIndex]->id,
                    'membership_start_date' => $startDate->format('Y-m-d'),
                    'joining_year' => $startDate->year,
                    'created_by' => 1,
                    'updated_by' => 1,
                ])
            );
        }
    }
}
