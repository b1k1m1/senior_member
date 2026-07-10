<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\ReceiptType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReceiptController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.receipts.index');
    }

    public function ajax(Request $request)
    {
        $query = Receipt::with(['receiptType'])
            ->select('receipts.*');

        /*
        |--------------------------------------------------------------------------
        | DataTables Search
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');

            $query->where(function ($q) use ($searchValue) {
                $q->where('receipts.receipt_no', 'like', '%' . $searchValue . '%')
                    ->orWhere('receipts.received_from', 'like', '%' . $searchValue . '%')
                    ->orWhere('receipts.payment_mode', 'like', '%' . $searchValue . '%')
                    ->orWhere('receipts.amount', 'like', '%' . $searchValue . '%')
                    ->orWhereHas('receiptType', function ($typeQuery) use ($searchValue) {
                        $typeQuery->where('name', 'like', '%' . $searchValue . '%');
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | DataTables Sorting
        |--------------------------------------------------------------------------
        */
        $columns = [
            0 => 'receipt_no',
            1 => 'receipt_type_id',
            2 => 'received_from',
            3 => 'amount',
            4 => 'payment_mode',
            5 => 'created_at',
        ];

        if ($request->has('order') && count($request->order) > 0) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'asc';
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

            if ($orderColumn === 'receipt_type_id') {
                $query->leftJoin('receipt_types', 'receipts.receipt_type_id', '=', 'receipt_types.id')
                    ->orderBy('receipt_types.name', $orderDir)
                    ->select('receipts.*');
            } else {
                $query->orderBy($orderColumn, $orderDir);
            }
        } else {
            $query->orderByRaw('CAST(receipts.receipt_no AS UNSIGNED) DESC');
        }

        /*
        |--------------------------------------------------------------------------
        | Counts
        |--------------------------------------------------------------------------
        */
        $totalRecords = Receipt::count();

        $filteredRecords = (clone $query)->count();

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        $start = (int) ($request->start ?? 0);
        $length = (int) ($request->length ?? 10);

        if ($length > 0) {
            $query->skip($start)->take($length);
        }

        $receipts = $query->get();

        $data = $receipts->map(function ($receipt) {
            return [
                'id' => $receipt->id,
                'receipt_no' => $receipt->receipt_no,
                'receipt_type' => $receipt->receiptType?->name,
                'received_from' => $receipt->received_from,
                'amount' => number_format((float) $receipt->amount, 2),
                'payment_mode' => $receipt->payment_mode,
                'created_at' => $receipt->created_at
                    ? $receipt->created_at->format('m/d/Y')
                    : '',
                'actions' => '
                    <a href="' . route('admin.receipts.show', $receipt->id) . '" class="btn btn-sm btn-info">View</a>
                    <a href="' . route('admin.receipts.edit', $receipt->id) . '" class="btn btn-sm btn-primary">Edit</a>
                    <a href="' . route('admin.receipts.print', $receipt->id) . '" target="_blank" class="btn btn-sm btn-success">Print</a>
                ',
            ];
        });

        return response()->json([
            'draw' => (int) ($request->draw ?? 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function create()
    {
        $receiptTypes = ReceiptType::where('is_active', 1)
            ->orderBy('id')
            ->get();

        $nextReceiptNo = $this->getNextReceiptNo();

        return view('admin.receipts.create', compact(
            'receiptTypes',
            'nextReceiptNo'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'receipt_no' => [
                'required',
                'regex:/^\d{6}$/',
                'unique:receipts,receipt_no',
            ],
            'receipt_type_id' => 'required|exists:receipt_types,id',
            'received_from' => 'required|string|max:255',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'payment_mode' => 'required|in:CASH,CHECK,CREDIT_CARD',
            'bank_name' => 'nullable|string|max:255',
            'check_date' => 'nullable|date',
            'check_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'donor_name' => 'nullable|string|max:255',
        ], [
            'receipt_no.regex' => 'Receipt number must be 6 digits only, for example 005001.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear check fields when payment mode is not CHECK
        |--------------------------------------------------------------------------
        */
        if ($validated['payment_mode'] !== 'CHECK') {
            $validated['bank_name'] = null;
            $validated['check_date'] = null;
            $validated['check_number'] = null;
        }

        $receipt = Receipt::create([
            'receipt_no' => $validated['receipt_no'],
            'receipt_type_id' => $validated['receipt_type_id'],
            'received_from' => $validated['received_from'],
            'address1' => $validated['address1'] ?? null,
            'address2' => $validated['address2'] ?? null,
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'zip' => $validated['zip'] ?? null,
            'county' => $validated['county'] ?? null,
            'bank_name' => $validated['bank_name'] ?? null,
            'check_date' => $validated['check_date'] ?? null,
            'check_number' => $validated['check_number'] ?? null,
            'payment_mode' => $validated['payment_mode'],
            'amount' => $validated['amount'],
            'remarks' => $validated['remarks'] ?? null,
            'donor_name' => $validated['donor_name'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.receipts.index')
            ->with('success', 'Receipt created successfully. Receipt No: ' . $receipt->receipt_no);
    }

    public function show(Receipt $receipt)
    {
        $receipt->load(['receiptType', 'creator']);

        return view('admin.receipts.show', compact('receipt'));
    }

    public function edit(Receipt $receipt)
    {
        $receiptTypes = ReceiptType::where('is_active', 1)
            ->orderBy('id')
            ->get();

        return view('admin.receipts.edit', compact(
            'receipt',
            'receiptTypes'
        ));
    }

    public function update(Request $request, Receipt $receipt)
    {
        $validated = $request->validate([
            'receipt_no' => [
                'required',
                'regex:/^\d{6}$/',
                Rule::unique('receipts', 'receipt_no')->ignore($receipt->id),
            ],
            'receipt_type_id' => 'required|exists:receipt_types,id',
            'received_from' => 'required|string|max:255',
            'address1' => 'nullable|string|max:255',
            'address2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:255',
            'county' => 'nullable|string|max:255',
            'payment_mode' => 'required|in:CASH,CHECK,CREDIT_CARD',
            'bank_name' => 'nullable|string|max:255',
            'check_date' => 'nullable|date',
            'check_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'donor_name' => 'nullable|string|max:255',
        ], [
            'receipt_no.regex' => 'Receipt number must be 6 digits only, for example 005001.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Clear check fields when payment mode is not CHECK
        |--------------------------------------------------------------------------
        */
        if ($validated['payment_mode'] !== 'CHECK') {
            $validated['bank_name'] = null;
            $validated['check_date'] = null;
            $validated['check_number'] = null;
        }

        $validated['updated_by'] = Auth::id();

        $receipt->update($validated);

        return redirect()->route('admin.receipts.index')
            ->with('success', 'Receipt updated successfully.');
    }

    public function destroy(Receipt $receipt)
    {
        $receipt->delete();

        return response()->json([
            'success' => 'Receipt deleted successfully.',
        ]);
    }

    public function print(Receipt $receipt)
    {
        $receipt->load(['receiptType', 'creator']);

        $org = \App\Models\Organization::first();

        $officeBearers = \App\Models\OfficeBearer::orderBy('display_order')
            ->get();

        return view('admin.receipts.print', compact(
            'receipt',
            'org',
            'officeBearers'
        ));
    }

    private function getNextReceiptNo(): string
    {
        $lastReceiptNo = Receipt::query()
            ->selectRaw('MAX(CAST(receipt_no AS UNSIGNED)) as max_receipt_no')
            ->value('max_receipt_no');

        $nextReceiptNo = ((int) $lastReceiptNo) + 1;

        return str_pad($nextReceiptNo, 6, '0', STR_PAD_LEFT);
    }
}
