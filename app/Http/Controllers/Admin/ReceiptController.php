<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Receipt;
use App\Models\ReceiptType;
use App\Models\MembershipType;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.receipts.index');
    }

    public function ajax(Request $request)
    {
        $query = Receipt::with(['receiptType', 'member', 'event'])->select('receipts.*');

        $columns = ['receipt_no', 'receipt_type_id', 'received_from', 'amount', 'payment_mode', 'created_at', 'actions'];
        
        if ($request->has('order') && count($request->order) > 0) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'asc';
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            
            if ($orderColumn === 'receipt_type_id') {
                $query->join('receipt_types', 'receipts.receipt_type_id', '=', 'receipt_types.id')
                      ->orderBy('receipt_types.name', $orderDir);
            } else {
                $query->orderBy($orderColumn, $orderDir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        
        $totalRecords = Receipt::count();
        $filteredRecords = $query->count();
        
        $receipts = $query->skip($start)->take($length)->get();

        $data = $receipts->map(function ($receipt) {
            return [
                'id' => $receipt->id,
                'receipt_no' => $receipt->receipt_no,
                'receipt_type' => $receipt->receiptType?->name,
                'received_from' => $receipt->received_from,
                'amount' => $receipt->amount,
                'payment_mode' => $receipt->payment_mode,
                'created_at' => $receipt->created_at->format('Y-m-d'),
            ];
        });

        return response()->json([
            'draw' => $request->draw ?? 1,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function create()
    {
        $receiptTypes = ReceiptType::active()->orderBy('name')->get();
        $membershipTypes = MembershipType::active()->orderBy('name')->get();
        $events = Event::where('status', 'ACTIVE')->orderBy('title')->get();
        
        return view('admin.receipts.create', compact('receiptTypes', 'membershipTypes', 'events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receipt_type_id' => 'required|exists:receipt_types,id',
            'received_from' => 'required|string|max:255',
            'address1' => 'nullable|string|max:120',
            'address2' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:60',
            'state' => 'nullable|string|max:30',
            'zip' => 'nullable|string|max:15',
            'county' => 'nullable|string|max:50',
            'payment_mode' => 'required|in:CASH,CHECK,CREDIT_CARD',
            'bank_name' => 'nullable|string|max:100',
            'check_date' => 'nullable|date',
            'check_number' => 'nullable|string|max:30',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'event_id' => 'nullable|exists:events,id',
            'donor_name' => 'nullable|string|max:255',
            'member_first_name' => 'nullable|string|max:60',
            'member_last_name' => 'nullable|string|max:60',
            'member_spouse_first_name' => 'nullable|string|max:60',
            'member_spouse_last_name' => 'nullable|string|max:60',
            'member_spouse_dateofbirth' => 'nullable|date',
            'member_spouse_cell_phone' => 'nullable|string|max:25',
            'member_spouse_email' => 'nullable|email',
            'member_dateofbirth' => 'nullable|date',
            'member_email' => 'nullable|email',
            'member_phone' => 'nullable|string|max:25',
            'member_cell_phone' => 'nullable|string|max:25',
            'member_address1' => 'nullable|string|max:120',
            'member_address2' => 'nullable|string|max:120',
            'member_city' => 'nullable|string|max:60',
            'member_state' => 'nullable|string|max:30',
            'member_zip' => 'nullable|string|max:15',
            'member_county' => 'nullable|string|max:50',
            'membership_type_id' => 'nullable|exists:membership_types,id',
            'has_spouse' => 'nullable|boolean',
        ]);

        $receiptType = ReceiptType::find($validated['receipt_type_id']);
        
        // Generate receipt number
        $receiptNo = Receipt::generateReceiptNo();
        
        $receiptData = [
            'receipt_no' => $receiptNo,
            'receipt_type_id' => $validated['receipt_type_id'],
            'received_from' => $validated['received_from'],
            'address1' => $validated['address1'] ?? null,
            'address2' => $validated['address2'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'zip' => $validated['zip'] ?? null,
            'county' => $validated['county'] ?? null,
            'payment_mode' => $validated['payment_mode'],
            'bank_name' => $validated['bank_name'] ?? null,
            'check_date' => $validated['check_date'] ?? null,
            'check_number' => $validated['check_number'] ?? null,
            'amount' => $validated['amount'],
            'remarks' => $validated['remarks'] ?? null,
            'event_id' => $validated['event_id'] ?? null,
            'donor_name' => $validated['donor_name'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ];

        // Handle membership receipt
        if ($receiptType->code === 'MEMBERSHIP' && !empty($validated['member_first_name'])) {
            $membershipTypeId = $validated['membership_type_id'];
            $hasSpouse = !empty($validated['has_spouse']) && $validated['has_spouse'];
            $joiningYear = date('Y');
            
            // Get last member number
            $lastMember = Member::where('member_no', 'like', 'L%')->orderBy('member_no', 'desc')->first();
            $lastNumber = $lastMember ? (int) substr($lastMember->member_no, 1) : 0;
            
            // Create primary member
            $memberNo1 = 'L' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
            $member1 = Member::create([
                'member_no' => $memberNo1,
                'first_name' => $validated['member_first_name'],
                'last_name' => $validated['member_last_name'],
                'spouse_first_name' => $hasSpouse ? ($validated['member_spouse_first_name'] ?? null) : null,
                'spouse_last_name' => $hasSpouse ? ($validated['member_spouse_last_name'] ?? null) : null,
                'dateofbirth' => $validated['member_dateofbirth'] ?? null,
                'email' => $validated['member_email'] ?? null,
                'phone' => $validated['member_phone'] ?? null,
                'cell_phone' => $validated['member_cell_phone'] ?? null,
                'address1' => $validated['member_address1'] ?? null,
                'address2' => $validated['member_address2'] ?? null,
                'city' => $validated['member_city'] ?? null,
                'state' => $validated['member_state'] ?? null,
                'zip' => $validated['member_zip'] ?? null,
                'county' => $validated['member_county'] ?? null,
                'membership_type_id' => $membershipTypeId,
                'membership_start_date' => now()->toDateString(),
                'joining_year' => $joiningYear,
                'status' => 'ACTIVE',
                'receipt_no' => $receiptNo,
                'amount' => $validated['amount'],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            $receiptData['member_id'] = $member1->id;
            $receiptData['membership_type_id'] = $membershipTypeId;
            $receiptData['has_spouse'] = $hasSpouse;

            // Create spouse member if applicable
            if ($hasSpouse && !empty($validated['member_spouse_first_name'])) {
                $memberNo2 = 'L' . str_pad($lastNumber + 2, 5, '0', STR_PAD_LEFT);
                Member::create([
                    'member_no' => $memberNo2,
                    'first_name' => $validated['member_spouse_first_name'],
                    'last_name' => $validated['member_spouse_last_name'],
                    'spouse_first_name' => $validated['member_first_name'],
                    'spouse_last_name' => $validated['member_last_name'],
                    'dateofbirth' => $validated['member_spouse_dateofbirth'] ?? null,
                    'email' => $validated['member_spouse_email'] ?? null,
                    'phone' => $validated['member_spouse_cell_phone'] ?? null,
                    'cell_phone' => $validated['member_spouse_cell_phone'] ?? null,
                    'address1' => $validated['member_address1'] ?? null,
                    'address2' => $validated['member_address2'] ?? null,
                    'city' => $validated['member_city'] ?? null,
                    'state' => $validated['member_state'] ?? null,
                    'zip' => $validated['member_zip'] ?? null,
                    'county' => $validated['member_county'] ?? null,
                    'membership_type_id' => $membershipTypeId,
                    'membership_start_date' => now()->toDateString(),
                    'joining_year' => $joiningYear,
                    'status' => 'ACTIVE',
                    'receipt_no' => $receiptNo,
                    'amount' => $validated['amount'],
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        }

        $receipt = Receipt::create($receiptData);

        return redirect()->route('admin.receipts.index')
            ->with('success', 'Receipt created successfully. Receipt No: ' . $receiptNo);
    }

    public function show(Receipt $receipt)
    {
        $receipt->load(['receiptType', 'member', 'event', 'membershipType', 'creator']);
        return view('admin.receipts.show', compact('receipt'));
    }

    public function edit(Receipt $receipt)
    {
        $receiptTypes = ReceiptType::active()->orderBy('name')->get();
        $membershipTypes = MembershipType::active()->orderBy('name')->get();
        $events = Event::orderBy('title')->get();
        
        $receipt->load('member');
        
        // Load spouse if exists (member with same receipt_no but different from primary member)
        $spouse = null;
        if ($receipt->member && $receipt->receipt_no) {
            $spouse = Member::where('receipt_no', $receipt->receipt_no)
                ->where('id', '!=', $receipt->member_id)
                ->first();
        }
        
        return view('admin.receipts.edit', compact('receipt', 'receiptTypes', 'membershipTypes', 'events', 'spouse'));
    }

    public function update(Request $request, Receipt $receipt)
    {
        $validated = $request->validate([
            'receipt_type_id' => 'required|exists:receipt_types,id',
            'received_from' => 'required|string|max:255',
            'address1' => 'nullable|string|max:120',
            'address2' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:60',
            'state' => 'nullable|string|max:30',
            'zip' => 'nullable|string|max:15',
            'county' => 'nullable|string|max:50',
            'payment_mode' => 'required|in:CASH,CHECK,CREDIT_CARD',
            'bank_name' => 'nullable|string|max:100',
            'check_date' => 'nullable|date',
            'check_number' => 'nullable|string|max:30',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'event_id' => 'nullable|exists:events,id',
            'donor_name' => 'nullable|string|max:255',
            'membership_type_id' => 'nullable|exists:membership_types,id',
            'has_spouse' => 'nullable|boolean',
            'member_first_name' => 'nullable|string|max:60',
            'member_last_name' => 'nullable|string|max:60',
            'member_dateofbirth' => 'nullable|date',
            'member_email' => 'nullable|email',
            'member_phone' => 'nullable|string|max:25',
            'member_cell_phone' => 'nullable|string|max:25',
            'member_address1' => 'nullable|string|max:120',
            'member_address2' => 'nullable|string|max:120',
            'member_city' => 'nullable|string|max:60',
            'member_state' => 'nullable|string|max:30',
            'member_zip' => 'nullable|string|max:15',
            'member_county' => 'nullable|string|max:50',
            'member_spouse_first_name' => 'nullable|string|max:60',
            'member_spouse_last_name' => 'nullable|string|max:60',
            'member_spouse_dateofbirth' => 'nullable|date',
            'member_spouse_cell_phone' => 'nullable|string|max:25',
            'member_spouse_email' => 'nullable|email',
        ]);

        // Convert empty strings to null for nullable fields
        $nullableFields = [
            'dateofbirth', 'email', 'phone', 'cell_phone',
            'address1', 'address2', 'city', 'state', 
            'zip', 'county', 'bank_name', 'check_number', 'check_date',
            'member_dateofbirth', 'member_email', 'member_phone', 'member_cell_phone',
            'member_address1', 'member_address2', 'member_city', 'member_state', 
            'member_zip', 'member_county', 'member_spouse_dateofbirth', 
            'member_spouse_cell_phone', 'member_spouse_email'
        ];
        foreach ($nullableFields as $field) {
            if (isset($validated[$field]) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        $validated['updated_by'] = Auth::id();
        $receipt->update($validated);

        // Update primary member if exists
        if ($receipt->member && !empty($validated['member_first_name'])) {
            $receipt->member->update([
                'first_name' => $validated['member_first_name'],
                'last_name' => $validated['member_last_name'],
                'dateofbirth' => $validated['member_dateofbirth'] ?? null,
                'email' => $validated['member_email'] ?? null,
                'phone' => $validated['member_phone'] ?? null,
                'cell_phone' => $validated['member_cell_phone'] ?? null,
                'address1' => $validated['member_address1'] ?? null,
                'address2' => $validated['member_address2'] ?? null,
                'city' => $validated['member_city'] ?? null,
                'state' => $validated['member_state'] ?? null,
                'zip' => $validated['member_zip'] ?? null,
                'county' => $validated['member_county'] ?? null,
                'membership_type_id' => $validated['membership_type_id'] ?? null,
                'updated_by' => Auth::id(),
            ]);
        }

        // Update or create spouse
        if (!empty($validated['has_spouse']) && !empty($validated['member_spouse_first_name'])) {
            // Find existing spouse
            $spouse = Member::where('receipt_no', $receipt->receipt_no)
                ->where('id', '!=', $receipt->member_id)
                ->first();

            if ($spouse) {
                // Update existing spouse
                $spouse->update([
                    'first_name' => $validated['member_spouse_first_name'],
                    'last_name' => $validated['member_spouse_last_name'],
                    'dateofbirth' => $validated['member_spouse_dateofbirth'] ?? null,
                    'email' => $validated['member_spouse_email'] ?? null,
                    'cell_phone' => $validated['member_spouse_cell_phone'] ?? null,
                    'address1' => $validated['member_address1'] ?? null,
                    'address2' => $validated['member_address2'] ?? null,
                    'city' => $validated['member_city'] ?? null,
                    'state' => $validated['member_state'] ?? null,
                    'zip' => $validated['member_zip'] ?? null,
                    'county' => $validated['member_county'] ?? null,
                    'updated_by' => Auth::id(),
                ]);
            } else {
                // Create new spouse
                $lastMember = Member::where('member_no', 'like', 'L%')->orderBy('member_no', 'desc')->first();
                $lastNumber = $lastMember ? (int) substr($lastMember->member_no, 1) : 0;
                $memberNo2 = 'L' . str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
                
                Member::create([
                    'member_no' => $memberNo2,
                    'first_name' => $validated['member_spouse_first_name'],
                    'last_name' => $validated['member_spouse_last_name'],
                    'spouse_first_name' => $validated['member_first_name'],
                    'spouse_last_name' => $validated['member_last_name'],
                    'dateofbirth' => $validated['member_spouse_dateofbirth'] ?? null,
                    'email' => $validated['member_spouse_email'] ?? null,
                    'cell_phone' => $validated['member_spouse_cell_phone'] ?? null,
                    'address1' => $validated['member_address1'] ?? null,
                    'address2' => $validated['member_address2'] ?? null,
                    'city' => $validated['member_city'] ?? null,
                    'state' => $validated['member_state'] ?? null,
                    'zip' => $validated['member_zip'] ?? null,
                    'county' => $validated['member_county'] ?? null,
                    'membership_type_id' => $validated['membership_type_id'],
                    'membership_start_date' => $receipt->member?->membership_start_date ?? now()->toDateString(),
                    'joining_year' => date('Y'),
                    'status' => 'ACTIVE',
                    'receipt_no' => $receipt->receipt_no,
                    'amount' => $validated['amount'],
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]);
            }
        }

        return redirect()->route('admin.receipts.index')
            ->with('success', 'Receipt updated successfully.');
    }

    public function destroy(Receipt $receipt)
    {
        $receipt->delete();
        return response()->json(['success' => 'Receipt deleted successfully.']);
    }

    public function print(Receipt $receipt)
    {
        $receipt->load(['receiptType', 'member', 'event', 'membershipType', 'creator']);
        
        $org = \App\Models\Organization::first();
        $officeBearers = \App\Models\OfficeBearer::orderBy('display_order')->get();
        
        return view('admin.receipts.print', compact('receipt', 'org', 'officeBearers'));
    }
}
