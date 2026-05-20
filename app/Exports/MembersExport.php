<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MembersExport implements FromCollection, WithHeadings
{
    protected $members;

    public function __construct($members)
    {
        $this->members = $members;
    }

    public function collection()
    {
        return $this->members->map(function ($member) {
            return [
                'Member No' => $member->member_no,
                'First Name' => $member->first_name,
                'Last Name' => $member->last_name,
                'Spouse First Name' => $member->spouse_first_name,
                'Spouse Last Name' => $member->spouse_last_name,
                'Date of Birth' => $member->dateofbirth ? \Carbon\Carbon::parse($member->dateofbirth)->format('m/d/Y') : '',
                'Email' => $member->email,
                'Phone' => $this->formatPhone($member->phone),
                'Cell Phone' => $this->formatPhone($member->cell_phone),
                'Address 1' => $member->address1,
                'Address 2' => $member->address2,
                'City' => $member->city,
                'State' => $member->state,
                'Zip' => $member->zip,
                'County' => $member->county,
                'Joining Year' => $member->joining_year,
                'Status' => $member->status,
                'Status Reason' => $member->status_reason,
                'Notes' => $member->notes,
                'Receipt No' => $member->receipt_no,
                'Amount' => $member->amount,
            ];
        });
    }

    private function formatPhone($phone)
    {
        if (!$phone) return '';
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 10) {
            return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6);
        }
        return $phone;
    }

    public function headings(): array
    {
        return [
            'Member No',
            'First Name',
            'Last Name',
            'Spouse First Name',
            'Spouse Last Name',
            'Date of Birth',
            'Email',
            'Phone',
            'Cell Phone',
            'Address 1',
            'Address 2',
            'City',
            'State',
            'Zip',
            'County',
            'Joining Year',
            'Status',
            'Status Reason',
            'Notes',
            'Receipt No',
            'Amount',
        ];
    }
}
