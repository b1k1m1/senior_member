<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Models\MembershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Imports\MembersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToArray;
use Illuminate\Support\Facades\DB;
use App\Models\Receipt;
use App\Models\ReceiptType;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.members.index');
    }

    public function ajax(Request $request)
    {
        //$searchColumn = $request->get('search_column', 'all');
        //$searchValue  = trim($request->get('search_value', ''));

        //$start  = (int) $request->get('start', 0);
        //$length = (int) $request->get('length', 10);

        // Safety limit
        //if ($length <= 0 || $length > 100) {
            //$length = 10;
        //}

        $searchColumn = $request->get('search_column', 'all');
        $searchValue  = trim($request->get('search_value', ''));

        $start  = max((int) $request->get('start', 0), 0);
        $length = (int) $request->get('length', 50);

        $allowedLengths = [25, 50, 100, 250, 500, 1000];

        if (!in_array($length, $allowedLengths, true)) {
            $length = 50;
        }

        /*
        |--------------------------------------------------------------------------
        | Base Query - select only columns needed by DataTables
        |--------------------------------------------------------------------------
        */
        $query = Member::query()
            ->select([
                'id',
                'member_no',
                'first_name',
                'last_name',
                'status',
                'phone',
                'city',
                'joining_year',
                'receipt_no',
                'photo_path',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Column-specific Search
        |--------------------------------------------------------------------------
        */
        $allowedSearchColumns = [
            'member_no',
            'first_name',
            'last_name',
            'phone',
            'city',
            'email',
            'receipt_no',
            'joining_year',
            'status',
        ];

        if ($searchValue !== '') {

            if ($searchColumn === 'all') {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('first_name', 'like', "%{$searchValue}%")
                    ->orWhere('last_name', 'like', "%{$searchValue}%")
                    ->orWhere('member_no', 'like', "%{$searchValue}%")
                    ->orWhere('phone', 'like', "%{$searchValue}%")
                    ->orWhere('city', 'like', "%{$searchValue}%")
                    ->orWhere('email', 'like', "%{$searchValue}%")
                    ->orWhere('receipt_no', 'like', "%{$searchValue}%");

                    // If user types a year like 2004, search joining_year exactly
                    if (is_numeric($searchValue)) {
                        $q->orWhere('joining_year', (int) $searchValue);
                    }
                });
            } elseif (in_array($searchColumn, $allowedSearchColumns, true)) {

                // joining_year should be exact match, not LIKE
                if ($searchColumn === 'joining_year') {
                    if (is_numeric($searchValue)) {
                        $query->where('joining_year', (int) $searchValue);
                    }
                } else {
                    $query->where($searchColumn, 'like', "%{$searchValue}%");
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Total / Filtered Count
        |--------------------------------------------------------------------------
        */
        $totalRecords = Member::count();

        // Clone before ordering/pagination
        $filteredRecords = (clone $query)->count();

        /*
        |--------------------------------------------------------------------------
        | Sorting
        |--------------------------------------------------------------------------
        */
        $columns = [
            'photo_path',
            'member_no',
            'last_name',
            'first_name',
            'phone',
            'city',
            'joining_year',
            'status',
            'receipt_no',
            'actions',
        ];

        $orderMap = [
            'member_no'    => 'member_no',
            'last_name'    => 'last_name',
            'first_name'   => 'first_name',
            'status'       => 'status',
            'phone'        => 'phone',
            'city'         => 'city',
            'joining_year' => 'joining_year',
            'receipt_no'   => 'receipt_no',
        ];

        if ($request->has('order') && count($request->order) > 0) {
            $orderColumnIndex = (int) $request->order[0]['column'];
            $orderDir = strtolower($request->order[0]['dir'] ?? 'asc');

            if (!in_array($orderDir, ['asc', 'desc'], true)) {
                $orderDir = 'asc';
            }

            $orderColumn = $columns[$orderColumnIndex] ?? 'last_name';
            $dbColumn = $orderMap[$orderColumn] ?? 'last_name';

            $query->orderBy($dbColumn, $orderDir);
        } else {
            $query->orderBy('last_name')->orderBy('first_name');
        }

        /*
        |--------------------------------------------------------------------------
        | Data
        |--------------------------------------------------------------------------
        */
        $members = $query
            ->skip($start)
            ->take($length)
            ->get();

        $data = $members->map(function ($member) {
            return [
                'id'           => $member->id,
                'member_no'    => $member->member_no,
                'first_name'   => $member->first_name,
                'last_name'    => $member->last_name,
                'status'       => $member->status,
                'phone'        => $member->phone,
                'city'         => $member->city,
                'joining_year' => $member->joining_year,
                'receipt_no'   => $member->receipt_no,
                'photo_path'   => $member->photo_path,
            ];
        });

        return response()->json([
            'draw'            => (int) $request->get('draw', 1),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ]);

    } // End Method

    public function create()
    {
        $membershipTypes = MembershipType::active()->orderBy('name')->get();

        $nextMemberNo = $this->getNextMemberNo();
        $nextSpouseMemberNo = $this->incrementMemberNo($nextMemberNo);
        $nextReceiptNo = $this->getNextReceiptNo();

        return view('admin.members.create', compact(
            'membershipTypes',
            'nextMemberNo',
            'nextSpouseMemberNo',
            'nextReceiptNo'
        ));

    } // End Method

    // public function store(StoreMemberRequest $request)

    // {
    //     'receipt_no' => 'required|regex:/^\d{6}$/|unique:receipts,receipt_no',
    //     $data = $request->validated();

    //     DB::beginTransaction();

    //     try {
    //         // Clean primary member number
    //         $data['member_no'] = strtoupper(preg_replace('/\s+/', '', $data['member_no']));

    //         $spouseFirstName = trim((string) $request->input('spouse_first_name'));
    //         $spouseLastName  = trim((string) $request->input('spouse_last_name'));

    //         $hasSpouse = $spouseFirstName !== '' || $spouseLastName !== '';

    //         $amounts = $this->calculateMemberAmounts($data['membership_type_id'], $hasSpouse);

    //         $data['amount'] = $amounts['primary_amount'];

    //         if ($request->hasFile('photo')) {
    //             $path = $request->file('photo')->store('members', 'public');
    //             $data['photo_path'] = $path;
    //         }

    //         $data['created_by'] = Auth::id();
    //         $data['updated_by'] = Auth::id();

    //         // Create primary member
    //         $primaryMember = Member::create($data);

    //         /*
    //             If spouse first name or spouse last name is entered,
    //             create spouse as a separate member record.
    //          */
    //         $spouseFirstName = trim((string) $request->input('spouse_first_name'));
    //         $spouseLastName  = trim((string) $request->input('spouse_last_name'));

    //         if ($spouseFirstName !== '' || $spouseLastName !== '') {

    //             $spouseMemberNo = trim((string) $request->input('spouse_member_no'));

    //             if ($spouseMemberNo === '') {
    //                 $spouseMemberNo = $this->incrementMemberNo($data['member_no']);
    //             }

    //             $spouseMemberNo = strtoupper(preg_replace('/\s+/', '', $spouseMemberNo));

    //             if ($spouseLastName === '') {
    //                 $spouseLastName = $data['last_name'];
    //             }

    //             $spouseData = $data;

    //             $spouseData['member_no'] = $spouseMemberNo;
    //             $spouseData['first_name'] = $spouseFirstName ?: 'Unknown';
    //             $spouseData['last_name'] = $spouseLastName;

    //             // Spouse's own personal/contact data
    //             $spouseData['dateofbirth'] = $request->input('spouse_dateofbirth') ?: null;
    //             $spouseData['email'] = $request->input('spouse_email') ?: null;
    //             $spouseData['cell_phone'] = $request->input('spouse_cell_phone') ?: null;

    //             // Share primary address/contact fields
    //             $spouseData['phone'] = $data['phone'] ?? null;
    //             $spouseData['address1'] = $data['address1'] ?? null;
    //             $spouseData['address2'] = $data['address2'] ?? null;
    //             $spouseData['city'] = $data['city'] ?? null;
    //             $spouseData['state'] = $data['state'] ?? null;
    //             $spouseData['zip'] = $data['zip'] ?? null;
    //             $spouseData['county'] = $data['county'] ?? null;

    //             // Do not store spouse fields again on spouse row
    //             $spouseData['spouse_first_name'] = null;
    //             $spouseData['spouse_last_name'] = null;

    //             // Same membership / receipt
    //             $spouseData['membership_type_id'] = $data['membership_type_id'];
    //             $spouseData['membership_start_date'] = $data['membership_start_date'] ?? null;
    //             $spouseData['joining_year'] = $data['joining_year'] ?? null;

    //             $spouseData['receipt_no'] = $data['receipt_no'] ?? null;
    //             $spouseData['status'] = $data['status'] ?? 'ACTIVE';
    //             $spouseData['status_reason'] = $data['status_reason'] ?? null;

    //             // Avoid duplicate photo and amount
    //             $spouseData['photo_path'] = null;
    //             $spouseData['amount'] = $amounts['spouse_amount'];

    //             $spouseData['created_by'] = Auth::id();
    //             $spouseData['updated_by'] = Auth::id();

    //             Member::create($spouseData);
    //         }

    //         DB::commit();

    //         return redirect()->route('admin.members.index')
    //             ->with('success', 'Member created successfully.');

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return redirect()
    //             ->back()
    //             ->withInput()
    //             ->with('error', 'Member creation failed: ' . $e->getMessage());
    //     }

    // } // End Method

    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Clean primary member number and receipt number
            |--------------------------------------------------------------------------
            */
            $data['member_no'] = strtoupper(preg_replace('/\s+/', '', $data['member_no']));

            if (!empty($data['receipt_no'])) {
                $data['receipt_no'] = preg_replace('/\D/', '', $data['receipt_no']);
                $data['receipt_no'] = str_pad($data['receipt_no'], 6, '0', STR_PAD_LEFT);
            }

            /*
            |--------------------------------------------------------------------------
            | Determine if spouse exists
            |--------------------------------------------------------------------------
            */
            $spouseFirstName = trim((string) $request->input('spouse_first_name'));
            $spouseLastName  = trim((string) $request->input('spouse_last_name'));

            $hasSpouse = $spouseFirstName !== '' || $spouseLastName !== '';

            /*
            |--------------------------------------------------------------------------
            | Calculate amounts
            |--------------------------------------------------------------------------
            */
            $amounts = $this->calculateMemberAmounts($data['membership_type_id'], $hasSpouse);

            $data['amount'] = $amounts['primary_amount'];

            /*
            |--------------------------------------------------------------------------
            | Upload primary member photo
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('members', 'public');
                $data['photo_path'] = $path;
            }

            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            /*
            |--------------------------------------------------------------------------
            | Create primary member
            |--------------------------------------------------------------------------
            */
            $primaryMember = Member::create($data);

            /*
            |--------------------------------------------------------------------------
            | Create spouse member if spouse first name or last name is entered
            |--------------------------------------------------------------------------
            */
            if ($spouseFirstName !== '' || $spouseLastName !== '') {

                $spouseMemberNo = trim((string) $request->input('spouse_member_no'));

                if ($spouseMemberNo === '') {
                    $spouseMemberNo = $this->incrementMemberNo($data['member_no']);
                }

                $spouseMemberNo = strtoupper(preg_replace('/\s+/', '', $spouseMemberNo));

                if ($spouseLastName === '') {
                    $spouseLastName = $data['last_name'];
                }

                $spouseData = $data;

                $spouseData['member_no'] = $spouseMemberNo;
                $spouseData['first_name'] = $spouseFirstName ?: 'Unknown';
                $spouseData['last_name'] = $spouseLastName;

                // Spouse's own personal/contact data
                $spouseData['dateofbirth'] = $request->input('spouse_dateofbirth') ?: null;
                $spouseData['email'] = $request->input('spouse_email') ?: null;
                $spouseData['cell_phone'] = $request->input('spouse_cell_phone') ?: null;

                // Share primary address/contact fields
                $spouseData['phone'] = $data['phone'] ?? null;
                $spouseData['address1'] = $data['address1'] ?? null;
                $spouseData['address2'] = $data['address2'] ?? null;
                $spouseData['city'] = $data['city'] ?? null;
                $spouseData['state'] = $data['state'] ?? null;
                $spouseData['zip'] = $data['zip'] ?? null;
                $spouseData['county'] = $data['county'] ?? null;

                // Do not store spouse fields again on spouse row
                $spouseData['spouse_first_name'] = null;
                $spouseData['spouse_last_name'] = null;

                // Same membership / receipt
                $spouseData['membership_type_id'] = $data['membership_type_id'];
                $spouseData['membership_start_date'] = $data['membership_start_date'] ?? null;
                $spouseData['joining_year'] = $data['joining_year'] ?? null;
                $spouseData['receipt_no'] = $data['receipt_no'] ?? null;
                $spouseData['status'] = $data['status'] ?? 'ACTIVE';
                $spouseData['status_reason'] = $data['status_reason'] ?? null;

                // Avoid duplicate photo and set spouse amount
                $spouseData['photo_path'] = null;
                $spouseData['amount'] = $amounts['spouse_amount'];

                $spouseData['created_by'] = Auth::id();
                $spouseData['updated_by'] = Auth::id();

                Member::create($spouseData);
            }

            /*
            |--------------------------------------------------------------------------
            | Create receipt row
            |--------------------------------------------------------------------------
            | This is important because next receipt number is generated from receipts table.
            | Without this insert, member create will keep showing the same receipt number.
            |--------------------------------------------------------------------------
            */
            if (!empty($data['receipt_no'])) {

                $receiptType = ReceiptType::where('code', 'MEMBERSHIP')->first();

                if (!$receiptType) {
                    throw new \Exception('Receipt type MEMBERSHIP was not found. Please create it in receipt_types table.');
                }

                $totalReceiptAmount = $amounts['primary_amount'] + $amounts['spouse_amount'];

                if (!empty($data['receipt_no'])) {

                    $receiptType = ReceiptType::where('code', 'MEMBERSHIP')->first();

                    if (!$receiptType) {
                        throw new \Exception('Receipt type MEMBERSHIP was not found. Please create it in receipt_types table.');
                    }

                    $paymentMode = $data['payment_mode'] ?? 'CASH';

                    $bankName = null;
                    $checkNumber = null;
                    $checkDate = null;

                    if ($paymentMode === 'CHECK') {
                        $bankName = $data['bank_name'] ?? null;
                        $checkNumber = $data['check_number'] ?? null;
                        $checkDate = $data['check_date'] ?? null;
                    }

                    $totalReceiptAmount = $amounts['primary_amount'] + $amounts['spouse_amount'];

                    Receipt::create([
                        'receipt_no' => $data['receipt_no'],
                        'receipt_type_id' => $receiptType->id,
                        'received_from' => trim($data['first_name'] . ' ' . $data['last_name']),
                        'address1' => $data['address1'] ?? null,
                        'address2' => $data['address2'] ?? null,
                        'city' => $data['city'] ?? null,
                        'state' => $data['state'] ?? null,
                        'zip' => $data['zip'] ?? null,
                        'county' => $data['county'] ?? null,
                        'bank_name' => $bankName,
                        'check_date' => $checkDate,
                        'check_number' => $checkNumber,
                        'payment_mode' => $paymentMode,
                        'amount' => $totalReceiptAmount,
                        'remarks' => 'Membership receipt created from member entry.',
                        'member_id' => $primaryMember->id,
                        'membership_type_id' => $data['membership_type_id'] ?? null,
                        'has_spouse' => $hasSpouse ? 1 : 0,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('admin.members.index')
                ->with('success', 'Member created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Member creation failed: ' . $e->getMessage());
        }

    } // End Method

    public function show(Member $member)
    {
        $member->load(['membershipType', 'payments' => function ($query) {
            $query->orderBy('payment_date', 'desc');
        }]);

        return view('admin.members.show', compact('member'));
    }

    public function edit(Member $member)
    {
        $membershipTypes = MembershipType::active()->orderBy('name')->get();

        $spouseMember = null;

        if (!empty($member->receipt_no)) {
            $spouseMember = Member::where('receipt_no', $member->receipt_no)
                ->where('id', '!=', $member->id)
                ->orderBy('member_no')
                ->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Load receipt record connected with this member receipt_no
        |--------------------------------------------------------------------------
        */
        $receipt = null;

        if (!empty($member->receipt_no)) {
            $receipt = Receipt::where('receipt_no', $member->receipt_no)->first();
        }

        return view('admin.members.edit', compact(
            'member',
            'membershipTypes',
            'spouseMember',
            'receipt'
        ));

    } // End Method

    public function update(UpdateMemberRequest $request, Member $member)
    {
        $data = $request->validated();

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | Clean primary member number and receipt number
            |--------------------------------------------------------------------------
            */
            $oldReceiptNo = $member->receipt_no;

            $data['member_no'] = strtoupper(preg_replace('/\s+/', '', $data['member_no']));

            if (!empty($data['receipt_no'])) {
                $data['receipt_no'] = preg_replace('/\D/', '', $data['receipt_no']);
                $data['receipt_no'] = str_pad($data['receipt_no'], 6, '0', STR_PAD_LEFT);
            }

            $spouseFirstName = trim((string) $request->input('spouse_first_name'));
            $spouseLastName  = trim((string) $request->input('spouse_last_name'));
            $spouseMemberNo  = trim((string) $request->input('spouse_member_no'));
            $spouseMemberId  = $request->input('spouse_member_id');

            $hasSpouse = $spouseFirstName !== '' || $spouseLastName !== '' || $spouseMemberNo !== '' || $spouseMemberId;

            /*
            |--------------------------------------------------------------------------
            | Calculate amounts
            |--------------------------------------------------------------------------
            */
            $amounts = $this->calculateMemberAmounts($data['membership_type_id'], $hasSpouse);

            $data['amount'] = $amounts['primary_amount'];

            /*
            |--------------------------------------------------------------------------
            | Upload photo
            |--------------------------------------------------------------------------
            */
            if ($request->hasFile('photo')) {
                if ($member->photo_path) {
                    Storage::disk('public')->delete($member->photo_path);
                }

                $path = $request->file('photo')->store('members', 'public');
                $data['photo_path'] = $path;
            }

            $data['updated_by'] = Auth::id();

            /*
            |--------------------------------------------------------------------------
            | Update primary member
            |--------------------------------------------------------------------------
            */
            $member->update($data);
            $member->refresh();

            /*
            |--------------------------------------------------------------------------
            | Spouse update/create logic
            |--------------------------------------------------------------------------
            */
            $spouseMemberNo = strtoupper(preg_replace('/\s+/', '', $spouseMemberNo));

            if ($spouseFirstName !== '' || $spouseLastName !== '' || $spouseMemberNo !== '') {

                if ($spouseMemberNo === '') {
                    $spouseMemberNo = $this->incrementMemberNo($member->member_no);
                }

                if ($spouseLastName === '') {
                    $spouseLastName = $member->last_name;
                }

                /*
                |--------------------------------------------------------------------------
                | Check duplicate spouse member no, ignoring spouse's own record
                |--------------------------------------------------------------------------
                */
                $duplicateSpouseNo = Member::where('member_no', $spouseMemberNo)
                    ->when($spouseMemberId, function ($query) use ($spouseMemberId) {
                        $query->where('id', '!=', $spouseMemberId);
                    })
                    ->where('id', '!=', $member->id)
                    ->exists();

                if ($duplicateSpouseNo) {
                    DB::rollBack();

                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors([
                            'spouse_member_no' => 'This spouse member number already exists.',
                        ]);
                }

                $spouseData = [
                    'member_no'             => $spouseMemberNo,
                    'first_name'            => $spouseFirstName ?: 'Unknown',
                    'last_name'             => $spouseLastName,
                    'spouse_first_name'     => null,
                    'spouse_last_name'      => null,

                    // Spouse's own fields
                    'dateofbirth'           => $request->input('spouse_dateofbirth') ?: null,
                    'email'                 => $request->input('spouse_email') ?: null,
                    'cell_phone'            => $request->input('spouse_cell_phone') ?: null,

                    // Shared address/contact from primary
                    'phone'                 => $member->phone,
                    'address1'              => $member->address1,
                    'address2'              => $member->address2,
                    'city'                  => $member->city,
                    'state'                 => $member->state,
                    'zip'                   => $member->zip,
                    'county'                => $member->county,

                    'membership_type_id'    => $member->membership_type_id,
                    'membership_start_date' => $member->membership_start_date,
                    'joining_year'          => $member->joining_year,
                    'status'                => $member->status,
                    'status_reason'         => $member->status_reason,
                    'notes'                 => null,
                    'photo_path'            => null,
                    'receipt_no'            => $member->receipt_no,
                    'amount'                => $amounts['spouse_amount'],
                    'updated_by'            => Auth::id(),
                ];

                if ($spouseMemberId) {
                    $spouseMember = Member::find($spouseMemberId);

                    if ($spouseMember) {
                        $spouseMember->update($spouseData);
                    }
                } else {
                    $spouseData['created_by'] = Auth::id();

                    Member::create($spouseData);
                }

                /*
                |--------------------------------------------------------------------------
                | Also keep spouse info on primary record for reference
                |--------------------------------------------------------------------------
                */
                $member->update([
                    'spouse_first_name' => $spouseFirstName,
                    'spouse_last_name'  => $spouseLastName,
                    'updated_by'        => Auth::id(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Update spouse receipt_no if receipt number was changed
            |--------------------------------------------------------------------------
            */
            if ($oldReceiptNo !== $member->receipt_no) {
                Member::where('receipt_no', $oldReceiptNo)
                    ->where('id', '!=', $member->id)
                    ->update([
                        'receipt_no' => $member->receipt_no,
                        'updated_by' => Auth::id(),
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Update or create receipt row
            |--------------------------------------------------------------------------
            */
            if (!empty($member->receipt_no)) {

                $receiptType = ReceiptType::where('code', 'MEMBERSHIP')->first();

                if (!$receiptType) {
                    throw new \Exception('Receipt type MEMBERSHIP was not found. Please create it in receipt_types table.');
                }

                $paymentMode = $data['payment_mode'] ?? 'CASH';

                $bankName = null;
                $checkNumber = null;
                $checkDate = null;

                if ($paymentMode === 'CHECK') {
                    $bankName = $data['bank_name'] ?? null;
                    $checkNumber = $data['check_number'] ?? null;
                    $checkDate = $data['check_date'] ?? null;
                }

                $totalReceiptAmount = $amounts['primary_amount'] + $amounts['spouse_amount'];

                $receipt = null;

                if (!empty($oldReceiptNo)) {
                    $receipt = Receipt::where('receipt_no', $oldReceiptNo)->first();
                }

                if (!$receipt) {
                    $receipt = Receipt::where('receipt_no', $member->receipt_no)->first();
                }

                $receiptData = [
                    'receipt_no' => $member->receipt_no,
                    'receipt_type_id' => $receiptType->id,
                    'received_from' => trim($member->first_name . ' ' . $member->last_name),
                    'address1' => $member->address1,
                    'address2' => $member->address2,
                    'city' => $member->city,
                    'state' => $member->state,
                    'zip' => $member->zip,
                    'county' => $member->county,
                    'bank_name' => $bankName,
                    'check_date' => $checkDate,
                    'check_number' => $checkNumber,
                    'payment_mode' => $paymentMode,
                    'amount' => $totalReceiptAmount,
                    'remarks' => 'Membership receipt updated from member entry.',
                    'member_id' => $member->id,
                    'membership_type_id' => $member->membership_type_id,
                    'has_spouse' => $hasSpouse ? 1 : 0,
                    'updated_by' => Auth::id(),
                ];

                if ($receipt) {
                    $receipt->update($receiptData);
                } else {
                    $receiptData['created_by'] = Auth::id();
                    Receipt::create($receiptData);
                }
            }

            DB::commit();

            return redirect()->route('admin.members.index')
                ->with('success', 'Member updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Member update failed: ' . $e->getMessage());
        }

    } // End Method

    public function destroy(Member $member)
    {
        if ($member->photo_path) {
            Storage::disk('public')->delete($member->photo_path);
        }

        $member->delete();

        return response()->json(['success' => 'Member deleted successfully.']);
    }

    public function search(Request $request)
    {
        $search = $request->get('q', '');

        $members = Member::active()
            ->where(function($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('member_no', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(50)
            ->get(['id', 'member_no', 'first_name', 'last_name', 'email'])
            ->map(function($member) {
                return [
                    'id' => $member->id,
                    'text' => $member->member_no . ' - ' . $member->first_name . ' ' . $member->last_name
                ];
            });

        return response()->json(['results' => $members]);
    }

    public function importForm()
    {
        return view('admin.members.import');

    } // End Method


    public function importStore(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $lifeType = MembershipType::where('name', 'LIFE')
            ->where('is_active', 1)
            ->first();

        if (!$lifeType) {
            return redirect()
                ->back()
                ->with('error', 'Default membership type LIFE was not found or is inactive.');
        }

        try {
            Excel::import(
                new MembersImport($lifeType->id),
                $request->file('excel_file')
            );

            return redirect()
                ->back()
                ->with('success', 'Members imported successfully.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }

    } // End Method

    public function importPreview(Request $request)
    {
        $request->validate([
            'excel_file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        try {
            $file = $request->file('excel_file');

            // Store file temporarily in storage/app/temp-imports
            $path = $file->store('temp-imports', 'local');
            $request->session()->put('members_import_file', $path);
            $request->session()->save();

            // Save path in session for confirm step
            session(['members_import_file' => $path]);

            // Use Storage::path instead of manually building storage_path()
            $fullPath = Storage::disk('local')->path($path);

            $rows = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
                public function array(array $array)
                {
                    return $array;
                }
            }, $fullPath);

            $sheetRows = $rows[0] ?? [];

            if (count($sheetRows) <= 1) {
                return redirect()
                    ->back()
                    ->with('error', 'Excel file does not contain any data rows.');
            }

            // Data rows only, skip first header row
            $dataRows = array_slice($sheetRows, 1);

            $totalRows = 0;
            $previewRows = [];
            $previewLimit = 100;

            foreach ($dataRows as $index => $row) {

                // Skip blank rows
                if (empty(array_filter($row))) {
                    continue;
                }

                $totalRows++;

                // Only preview first 100 valid rows
                if (count($previewRows) >= $previewLimit) {
                    continue;
                }

                // Excel row number = index + 2 because row 1 is header
                $rowNumber = $index + 2;

                $previewRows[] = [
                    'row_number'      => $rowNumber,
                    'id'              => $row[0] ?? null,
                    'listing_no'      => $row[1] ?? null,
                    'receipt_no'      => $row[2] ?? null,
                    'last_name'       => $row[3] ?? null,
                    'first_name'      => $row[4] ?? null,
                    'address'         => $row[5] ?? null,
                    'city'            => $row[6] ?? null,
                    'state'           => $row[7] ?? null,
                    'zip_code'        => $row[8] ?? null,
                    'county'          => $row[9] ?? null,
                    'date_of_birth'   => $row[10] ?? null,
                    'phone_home'      => $row[11] ?? null,
                    'phone_cell'      => $row[12] ?? null,
                    'fee'             => $row[13] ?? null,
                    'email_addresses' => $row[14] ?? null,
                    'joining_year'    => $row[15] ?? null,
                ];
            }

            return view('admin.members.import_preview', [
                'previewRows'  => $previewRows,
                'totalRows'    => $totalRows,
                'previewLimit' => $previewLimit,
            ]);

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Preview failed: ' . $e->getMessage());
        }

    } // End Method

    public function importConfirm(Request $request)
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $path = session('members_import_file');

        if (!$path || !Storage::disk('local')->exists($path)) {
            return redirect()
                ->route('admin.members.import.form')
                ->with('error', 'Import file not found. Please upload the Excel file again.');
        }

        $lifeType = MembershipType::where('name', 'LIFE')
            ->where('is_active', 1)
            ->first();

        if (!$lifeType) {
            return redirect()
                ->route('admin.members.import.form')
                ->with('error', 'Default membership type LIFE was not found or is inactive.');
        }

        try {
            $fullPath = Storage::disk('local')->path($path);

            $import = new MembersImport($lifeType->id);

            Excel::import($import, $fullPath);

            $results = $import->getResults();

            // Delete temporary file only AFTER successful import
            Storage::disk('local')->delete($path);

            // Clear session only AFTER successful import
            session()->forget('members_import_file');

            return redirect()
                ->route('admin.members.import.form')
                ->with(
                    'success',
                    'Import completed. Inserted: ' . $results['inserted'] .
                    ', Updated: ' . $results['updated'] .
                    ', Failed: ' . $results['failed']
                )
                ->with('import_errors', $results['errors']);

        } catch (\Exception $e) {
            return redirect()
                ->route('admin.members.import.form')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }

    } // End Method

    private function getNextMemberNo(): string
    {
        $lastNumber = DB::table('members')
            ->whereRaw("UPPER(REPLACE(member_no, ' ', '')) REGEXP '^L[0-9]+$'")
            ->selectRaw("
                MAX(
                    CAST(
                        SUBSTRING(UPPER(REPLACE(member_no, ' ', '')), 2)
                        AS UNSIGNED
                    )
                ) as max_no
            ")
            ->value('max_no');

        $nextNumber = ((int) $lastNumber) + 1;

        return 'L' . $nextNumber;

    } // End Method

    private function incrementMemberNo(string $memberNo): string
    {
        $clean = strtoupper(preg_replace('/\s+/', '', $memberNo));

        if (preg_match('/^L([0-9]+)$/', $clean, $matches)) {
            return 'L' . ((int) $matches[1] + 1);
        }

        return $this->getNextMemberNo();

    } // End Method

    private function calculateMemberAmounts($membershipTypeId, bool $hasSpouse): array
    {
        $membershipType = MembershipType::find($membershipTypeId);

        $totalAmount = $membershipType ? (float) $membershipType->fee_amount : 0;

        if ($hasSpouse && $totalAmount > 0) {
            $splitAmount = round($totalAmount / 2, 2);

            return [
                'primary_amount' => $splitAmount,
                'spouse_amount'  => $splitAmount,
                'total_amount'   => $totalAmount,
            ];
        }

        return [
            'primary_amount' => $totalAmount,
            'spouse_amount'  => null,
            'total_amount'   => $totalAmount,
        ];

    } // End Method

    private function getNextReceiptNo(): string
    {
        $lastReceiptNo = \App\Models\Receipt::query()
            ->selectRaw('MAX(CAST(receipt_no AS UNSIGNED)) as max_receipt_no')
            ->value('max_receipt_no');

        $nextReceiptNo = ((int) $lastReceiptNo) + 1;

        return str_pad($nextReceiptNo, 6, '0', STR_PAD_LEFT);

    } // End Method
}
