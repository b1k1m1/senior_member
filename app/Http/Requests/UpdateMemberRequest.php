<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $member = $this->route('member');
        $memberId = is_object($member) ? $member->id : $member;

        return [
            'member_no' => [
                'required',
                'string',
                'max:20',
                Rule::unique('members', 'member_no')->ignore($memberId),
            ],

            'first_name' => 'required|string|max:60',
            'last_name' => 'required|string|max:60',

            'spouse_first_name' => 'nullable|string|max:60',
            'spouse_last_name' => 'nullable|string|max:60',
            'spouse_dateofbirth' => 'nullable|date',
            'spouse_email' => 'nullable|email|max:120',
            'spouse_cell_phone' => 'nullable|string|max:25',

            'dateofbirth' => 'nullable|date',

            'email' => 'nullable|email|max:120',
            'phone' => 'nullable|string|max:25',
            'cell_phone' => 'nullable|string|max:25',

            'address1' => 'nullable|string|max:120',
            'address2' => 'nullable|string|max:120',
            'city' => 'nullable|string|max:60',
            'state' => 'nullable|string|max:30',
            'zip' => 'nullable|string|max:15',

            // members.county is NOT NULL and varchar(30)
            'county' => 'required|string|max:30',

            'membership_type_id' => 'required|exists:membership_types,id',
            'membership_start_date' => 'nullable|date',

            'status' => 'required|in:ACTIVE,INACTIVE',
            'status_reason' => 'nullable|string|max:45',

            'notes' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',

            'receipt_no' => [
                'required',
                'regex:/^\d{6}$/',
                Rule::unique('receipts', 'receipt_no')->ignore(
                    optional(\App\Models\Receipt::where('receipt_no', $this->route('member')?->receipt_no)->first())->id
                ),
            ],

            'payment_mode' => 'required|in:CASH,CHECK,CREDIT_CARD',
            'bank_name' => 'nullable|string|max:255',
            'check_number' => 'nullable|string|max:255',
            'check_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'member_no.required' => 'Member number is required.',
            'spouse_member_id' => 'nullable|integer|exists:members,id',
            'spouse_member_no' => 'nullable|string|max:20',
            'member_no.unique' => 'This member number already exists.',

            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',

            'county.required' => 'County is required.',

            'membership_type_id.required' => 'Membership type is required.',
            'membership_type_id.exists' => 'Selected membership type is invalid.',
            'receipt_no.regex' => 'Receipt number must be 6 digits only, for example 005001.',
            'receipt_no.unique' => 'This receipt number already exists.',
        ];
    }
}
