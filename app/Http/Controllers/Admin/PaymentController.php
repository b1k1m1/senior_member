<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.payments.index');
    }

    public function ajax(Request $request)
    {
        $query = Payment::with('member')->select('payments.*');

        // Sorting
        $columns = ['payment_date', 'member_name', 'amount', 'method', 'receipt_no', 'actions'];
        if ($request->has('order') && count($request->order) > 0) {
            $orderColumnIndex = $request->order[0]['column'];
            $orderDir = $request->order[0]['dir'] ?? 'asc';
            $orderColumn = $columns[$orderColumnIndex] ?? 'payment_date';
            
            if ($orderColumn === 'member_name') {
                $query->join('members', 'payments.member_id', '=', 'members.id')
                      ->orderBy('members.last_name', $orderDir)
                      ->orderBy('members.first_name', $orderDir);
            } else {
                $query->orderBy($orderColumn, $orderDir);
            }
        } else {
            $query->orderBy('payment_date', 'desc');
        }

        $start = $request->start ?? 0;
        $length = $request->length ?? 10;
        
        $totalRecords = Payment::count();
        $filteredRecords = $query->count();
        
        $payments = $query->skip($start)->take($length)->get();

        $data = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'payment_date' => $payment->payment_date->format('Y-m-d'),
                'member_name' => $payment->member->full_name,
                'amount' => $payment->amount,
                'method' => $payment->method,
                'receipt_no' => $payment->receipt_no,
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
        $members = Member::orderBy('last_name')->orderBy('first_name')->get();
        return view('admin.payments.create', compact('members'));
    }

    public function store(StorePaymentRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        Payment::create($data);

        return redirect()->route('admin.payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    public function createForMember(Member $member)
    {
        return view('admin.payments.create-for-member', compact('member'));
    }

    public function storeForMember(Request $request, Member $member)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:CASH,CHECK,CARD,OTHER',
            'receipt_no' => 'nullable|string|max:30',
            'remarks' => 'nullable|string|max:255',
        ]);

        $data = $request->all();
        $data['member_id'] = $member->id;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        Payment::create($data);

        return redirect()->route('admin.members.show', $member->id)
            ->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json(['success' => 'Payment deleted successfully.']);
    }
}
