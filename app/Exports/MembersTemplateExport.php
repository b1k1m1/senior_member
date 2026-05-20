<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MembersTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([
            [
                'L0001',
                'John',
                'Doe',
                'Jane',
                'Doe',
                'john@example.com',
                '555-1234',
                '123 Main St',
                '',
                'New York',
                'NY',
                '10001',
                'ANNUAL',
                '2024-01-15',
                'ACTIVE',
                'RCP001',
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'member_no',
            'first_name',
            'last_name',
            'spouse_first_name',
            'spouse_last_name',
            'email',
            'phone',
            'address1',
            'address2',
            'city',
            'state',
            'zip',
            'membership_type_name',
            'membership_start_date',
            'status',
            'receipt_no',
        ];
    }
}
