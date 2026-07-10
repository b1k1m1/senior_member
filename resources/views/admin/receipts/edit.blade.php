@extends('admin.admin_dashboard')

@section('title', 'Edit Receipt')
@section('page-title', 'Edit Receipt')

@section('content')

<div class="card">
    <div class="card-body">

        <form action="{{ route('admin.receipts.update', $receipt->id) }}" method="POST">
            @csrf
            @method('PUT')

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Please fix the following error(s):</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row">

                {{-- Receipt No --}}
                <div class="col-md-3 mb-3">
                    <label class="form-label">Receipt No *</label>
                    <input type="text"
                           name="receipt_no"
                           id="receipt_no"
                           class="form-control"
                           value="{{ old('receipt_no', $receipt->receipt_no) }}"
                           maxlength="6"
                           required>
                    <small class="text-muted">Numeric only, example: 005001</small>
                </div>

                {{-- Receipt Type --}}
                <div class="col-md-3 mb-3">
                    <label class="form-label">Receipt Type *</label>
                    <select name="receipt_type_id"
                            id="receipt_type_id"
                            class="form-select"
                            required>
                        <option value="">Select Type</option>

                        @foreach($receiptTypes as $type)
                            <option value="{{ $type->id }}"
                                    data-code="{{ $type->code }}"
                                    {{ old('receipt_type_id', $receipt->receipt_type_id) == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach

                    </select>
                </div>

                {{-- Received From --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Received From *</label>
                    <input type="text"
                           name="received_from"
                           class="form-control"
                           value="{{ old('received_from', $receipt->received_from) }}"
                           maxlength="255"
                           required>
                </div>

                {{-- Address 1 --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address Line 1</label>
                    <input type="text"
                           name="address1"
                           class="form-control"
                           value="{{ old('address1', $receipt->address1) }}"
                           maxlength="255">
                </div>

                {{-- Address 2 --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address Line 2</label>
                    <input type="text"
                           name="address2"
                           class="form-control"
                           value="{{ old('address2', $receipt->address2) }}"
                           maxlength="255">
                </div>

                {{-- City --}}
                <div class="col-md-3 mb-3">
                    <label class="form-label">City</label>
                    <input type="text"
                           name="city"
                           class="form-control"
                           value="{{ old('city', $receipt->city) }}"
                           maxlength="255">
                </div>

                {{-- State --}}
                <div class="col-md-3 mb-3">
                    <label class="form-label">State</label>
                    <input type="text"
                           name="state"
                           class="form-control"
                           value="{{ old('state', $receipt->state) }}"
                           maxlength="255">
                </div>

                {{-- ZIP --}}
                <div class="col-md-3 mb-3">
                    <label class="form-label">ZIP</label>
                    <input type="text"
                           name="zip"
                           class="form-control"
                           value="{{ old('zip', $receipt->zip) }}"
                           maxlength="255">
                </div>

                {{-- County --}}
                <div class="col-md-3 mb-3">
                    <label class="form-label">County</label>
                    <input type="text"
                           name="county"
                           class="form-control"
                           value="{{ old('county', $receipt->county) }}"
                           maxlength="255">
                </div>

                {{-- Payment Mode --}}
                <div class="col-md-3 mb-3">
                    <label class="form-label">Payment Mode *</label>
                    <select name="payment_mode"
                            id="payment_mode"
                            class="form-select"
                            required>
                        <option value="CASH"
                            {{ old('payment_mode', $receipt->payment_mode) == 'CASH' ? 'selected' : '' }}>
                            Cash
                        </option>

                        <option value="CHECK"
                            {{ old('payment_mode', $receipt->payment_mode) == 'CHECK' ? 'selected' : '' }}>
                            Check
                        </option>

                        <option value="CREDIT_CARD"
                            {{ old('payment_mode', $receipt->payment_mode) == 'CREDIT_CARD' ? 'selected' : '' }}>
                            Credit Card
                        </option>
                    </select>
                </div>

                {{-- Bank Name --}}
                <div class="col-md-3 mb-3 check-fields">
                    <label class="form-label">Bank Name</label>
                    <input type="text"
                           name="bank_name"
                           class="form-control"
                           value="{{ old('bank_name', $receipt->bank_name) }}"
                           maxlength="255">
                </div>

                {{-- Check Number --}}
                <div class="col-md-3 mb-3 check-fields">
                    <label class="form-label">Check Number</label>
                    <input type="text"
                           name="check_number"
                           class="form-control"
                           value="{{ old('check_number', $receipt->check_number) }}"
                           maxlength="255">
                </div>

                {{-- Check Date --}}
                <div class="col-md-3 mb-3 check-fields">
                    <label class="form-label">Check Date</label>
                    <input type="date"
                           name="check_date"
                           class="form-control"
                           value="{{ old('check_date', optional($receipt->check_date)->format('Y-m-d')) }}">
                </div>

                {{-- Amount --}}
                <div class="col-md-3 mb-3">
                    <label class="form-label">Amount *</label>
                    <input type="number"
                           name="amount"
                           class="form-control text-end"
                           value="{{ old('amount', $receipt->amount) }}"
                           min="0"
                           step="0.01"
                           required>
                </div>

                {{-- Donor Name --}}
                <div class="col-md-5 mb-3">
                    <label class="form-label">Donor Name</label>
                    <input type="text"
                           name="donor_name"
                           class="form-control"
                           value="{{ old('donor_name', $receipt->donor_name) }}"
                           maxlength="255">
                </div>

                {{-- Remarks --}}
                <div class="col-md-12 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks"
                              class="form-control"
                              rows="3">{{ old('remarks', $receipt->remarks) }}</textarea>
                </div>

            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('admin.receipts.index') }}" class="btn btn-secondary">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Update Receipt
                </button>
            </div>

        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function () {

        /*
        |--------------------------------------------------------------------------
        | Show check fields only when payment mode is CHECK
        |--------------------------------------------------------------------------
        */
        function toggleCheckFields() {
            let paymentMode = $('#payment_mode').val();

            if (paymentMode === 'CHECK') {
                $('.check-fields').show();
            } else {
                $('.check-fields').hide();

                $('input[name="bank_name"]').val('');
                $('input[name="check_number"]').val('');
                $('input[name="check_date"]').val('');
            }
        }

        $('#payment_mode').on('change', toggleCheckFields);
        toggleCheckFields();

        /*
        |--------------------------------------------------------------------------
        | Receipt No: numeric only, 6 digits
        |--------------------------------------------------------------------------
        */
        $('#receipt_no').on('input', function () {
            let value = $(this).val().replace(/\D/g, '');

            if (value.length > 6) {
                value = value.substring(0, 6);
            }

            $(this).val(value);
        });

        $('#receipt_no').on('blur', function () {
            let value = $(this).val().replace(/\D/g, '');

            if (value !== '') {
                $(this).val(value.padStart(6, '0'));
            }
        });

    });
</script>
@endpush
