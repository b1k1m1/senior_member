<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Normalize spouse checkbox
        |--------------------------------------------------------------------------
        |
        | An unchecked HTML checkbox is not submitted at all.
        | Convert that situation to 0 so the controller and validation always
        | receive a predictable value.
        |
        */

        $this->merge([
            'has_spouse' => $this->boolean('has_spouse') ? 1 : 0,
        ]);
    }

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Primary Member
            |--------------------------------------------------------------------------
            */

            'member_no' =>
            'required|string|max:20|unique:members,member_no',

            'first_name' =>
            'required|string|max:60',

            'last_name' =>
            'required|string|max:60',

            'dateofbirth' =>
            'nullable|date',

            /*
            |--------------------------------------------------------------------------
            | Spouse
            |--------------------------------------------------------------------------
            */

            'has_spouse' => [
                'required',
                'boolean',
            ],

            'spouse_member_no' => [
                'nullable',
                'string',
                'max:20',
                'unique:members,member_no',
            ],

            'spouse_first_name' => [
                'nullable',
                'required_if:has_spouse,1',
                'string',
                'max:60',
            ],

            'spouse_last_name' => [
                'nullable',
                'string',
                'max:60',
            ],

            'spouse_dateofbirth' => [
                'nullable',
                'date',
            ],

            'spouse_email' => [
                'nullable',
                'email',
                'max:120',
            ],

            'spouse_cell_phone' => [
                'nullable',
                'string',
                'max:25',
            ],

            /*
            |--------------------------------------------------------------------------
            | Contact Information
            |--------------------------------------------------------------------------
            */

            'email' => [
                'nullable',
                'email',
                'max:120',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:25',
            ],

            'cell_phone' => [
                'nullable',
                'string',
                'max:25',
            ],

            /*
            |--------------------------------------------------------------------------
            | Address
            |--------------------------------------------------------------------------
            */

            'address1' => [
                'nullable',
                'string',
                'max:120',
            ],

            'address2' => [
                'nullable',
                'string',
                'max:120',
            ],

            'city' => [
                'nullable',
                'string',
                'max:60',
            ],

            'state' => [
                'nullable',
                'string',
                'max:30',
            ],

            'zip' => [
                'nullable',
                'string',
                'max:15',
            ],

            /*
            |--------------------------------------------------------------------------
            | County
            |--------------------------------------------------------------------------
            |
            | members.county is NOT NULL and varchar(30)
            |
            */

            'county' => [
                'required',
                'string',
                'max:30',
            ],

            /*
            |--------------------------------------------------------------------------
            | Membership
            |--------------------------------------------------------------------------
            */

            'membership_type_id' => [
                'required',
                'exists:membership_types,id',
            ],

            'membership_start_date' => [
                'nullable',
                'date',
            ],

            'status' => [
                'required',
                'in:ACTIVE,INACTIVE',
            ],

            'status_reason' => [
                'nullable',
                'string',
                'max:45',
            ],

            /*
            |--------------------------------------------------------------------------
            | Other
            |--------------------------------------------------------------------------
            */

            'notes' => [
                'nullable',
                'string',
            ],

            'photo' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif',
                'max:2048',
            ],

            /*
            |--------------------------------------------------------------------------
            | Receipt
            |--------------------------------------------------------------------------
            */

            'receipt_no' => [
                'required',
                'regex:/^\d{6}$/',
                'unique:receipts,receipt_no',
            ],

            'payment_mode' => [
                'required',
                'in:CASH,CHECK,CREDIT_CARD',
            ],

            'bank_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'check_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'check_date' => [
                'nullable',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'member_no.required' =>
            'Member number is required.',

            'member_no.unique' =>
            'This member number already exists.',

            'first_name.required' =>
            'First name is required.',

            'last_name.required' =>
            'Last name is required.',

            'spouse_first_name.required_if' =>
            'Spouse first name is required when Add Spouse as Member is selected.',

            'spouse_member_no.unique' =>
            'This spouse member number already exists.',

            'county.required' =>
            'County is required.',

            'membership_type_id.required' =>
            'Membership type is required.',

            'membership_type_id.exists' =>
            'Selected membership type is invalid.',

            'receipt_no.required' =>
            'Receipt number is required.',

            'receipt_no.regex' =>
            'Receipt number must be 6 digits only, for example 005001.',

            'receipt_no.unique' =>
            'This receipt number already exists.',

            'payment_mode.required' =>
            'Payment mode is required.',
        ];
    }
}
