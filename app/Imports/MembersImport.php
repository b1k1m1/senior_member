<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\MembershipType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class MembersImport implements ToModel, WithHeadingRow
{
    protected $results = [
        'inserted' => 0,
        'updated'  => 0,
        'failed'   => 0,
        'errors'   => [],
    ];

    protected $membershipTypeId;

    public function __construct($membershipTypeId = null)
    {
        if ($membershipTypeId) {
            $this->membershipTypeId = $membershipTypeId;
        } else {
            $lifeType = MembershipType::where('name', 'LIFE')
                ->where('is_active', 1)
                ->first();

            $this->membershipTypeId = $lifeType?->id;
        }
    }

    public function model(array $row)
    {
        try {
            /*
                Excel headings will be converted by Laravel Excel.

                Listing No       => listing_no
                Receipt No        => receipt_no
                Last Name        => last_name
                First Name       => first_name
                Zip Code         => zip_code
                Date of Birth    => date_of_birth
                Phone(Home)      => phonehome
                Phone(cell)      => phonecell
                Email Addresses  => email_addresses
            */

            $memberNo = trim((string)($row['listing_no'] ?? ''));
            // Remove all spaces inside member number and make uppercase
            $memberNo = strtoupper(preg_replace('/\s+/', '', $memberNo));

            // Joining year - try heading first
            $joiningYearRaw = $row['joining_year'] ?? null;

            // Fallback: use column position from Excel
            // Your Excel column order is:
            // 0 ID, 1 Listing No, 2 Receipt N, 3 Joining year
            if ($joiningYearRaw === null || $joiningYearRaw === '') {
                $rowValues = array_values($row);
                $joiningYearRaw = $rowValues[3] ?? null;
            }

            $joiningYearRaw = trim((string)$joiningYearRaw);
            $joiningYearRaw = preg_replace('/[^0-9]/', '', $joiningYearRaw);

            $joiningYear = null;

            if ($joiningYearRaw !== '' && strlen($joiningYearRaw) === 4) {
                $joiningYear = (int)$joiningYearRaw;
            }

            if ($memberNo === '') {
                $this->results['failed']++;
                $this->results['errors'][] = 'Row skipped: Listing No is blank.';
                return null;
            }

            if (!$this->membershipTypeId) {
                $this->results['failed']++;
                $this->results['errors'][] = "Row with Listing No '{$memberNo}' failed: LIFE membership type not found.";
                return null;
            }

            $firstName = trim((string)($row['first_name'] ?? ''));
            $lastName  = trim((string)($row['last_name'] ?? ''));

            if ($firstName === '' && $lastName === '') {
                $this->results['failed']++;
                $this->results['errors'][] = "Row with Listing No '{$memberNo}' skipped: first name and last name are blank.";
                return null;
            }

            if ($firstName === '') {
                $firstName = 'Unknown';
            }

            if ($lastName === '') {
                $lastName = 'Unknown';
            }

            $county = trim((string)($row['county'] ?? ''));

            if ($county === '') {
                $county = 'Unknown';
            }

            $receiptNo  = trim((string)($row['receipt_no'] ?? ''));
            $address    = trim((string)($row['address'] ?? ''));
            $city       = trim((string)($row['city'] ?? ''));
            $state      = trim((string)($row['state'] ?? ''));
            $zip        = trim((string)($row['zip_code'] ?? ''));
            $county     = trim((string)($row['county'] ?? ''));
            $dob        = $this->formatDate($row['date_of_birth'] ?? null);
            $phone      = trim((string)($row['phonehome'] ?? ''));
            $cellPhone  = trim((string)($row['phonecell'] ?? ''));
            $amount     = $this->formatAmount($row['fee'] ?? null);
            $email      = trim((string)($row['email_addresses'] ?? ''));

            $memberData = [

                'member_no'             => $memberNo,
                'receipt_no'            => $receiptNo,
                'last_name'             => $lastName,
                'first_name'            => $firstName,
                'address1'              => $address,
                'city'                  => $city,
                'state'                 => $state,
                'zip'                   => $zip,
                'county'                => $county,
                'dateofbirth'           => $dob,
                'phone'                 => $phone,
                'cell_phone'            => $cellPhone,
                'amount'                => $amount,
                'email'                 => $email,
                'membership_type_id'    => $this->membershipTypeId,
                'membership_start_date' => null,
                'joining_year'          => $joiningYear,
                'status'                => 'ACTIVE',
                'updated_by'            => Auth::id(),
            ];

            $existingMember = Member::where('member_no', $memberNo)->first();

            if ($existingMember) {
                $existingMember->update($memberData);
                $this->results['updated']++;
            } else {
                $memberData['created_by'] = Auth::id();

                Member::create($memberData);
                $this->results['inserted']++;
            }

            return null;

        } catch (\Exception $e) {
            $this->results['failed']++;

            $memberNo = $row['listing_no'] ?? 'unknown';

            $this->results['errors'][] = "Row with Listing No '{$memberNo}' failed: " . $e->getMessage();

            return null;
        }
    }

    private function formatDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return ExcelDate::excelToDateTimeObject($value)->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');

        } catch (\Exception $e) {
            return null;
        }
    }

    private function formatAmount($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = str_replace(['$', ',', ' '], '', (string)$value);

        return is_numeric($value) ? number_format((float)$value, 2, '.', '') : null;
    }

    public function getResults(): array
    {
        return $this->results;
    }
}
