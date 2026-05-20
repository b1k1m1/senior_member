<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id' => 'required|exists:members,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:CASH,CHECK,CARD,OTHER',
            'receipt_no' => 'nullable|string|max:30',
            'remarks' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'member_id.required' => 'Member is required.',
            'payment_date.required' => 'Payment date is required.',
            'amount.required' => 'Amount is required.',
            'method.required' => 'Payment method is required.',
        ];
    }
}
