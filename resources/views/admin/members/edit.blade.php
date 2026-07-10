@extends('admin.admin_dashboard')

@section('title', 'Edit Member')
@section('page-title', 'Edit Member')

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Update failed. Please check the following:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.members.update', $member->id) }}" method="POST" enctype="multipart/form-data" id="member-form">
            @csrf
            @method('PUT')

            <ul class="nav nav-tabs" id="memberTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="personal-tab" data-bs-toggle="tab" data-bs-target="#personal" type="button">
                        <i class="fas fa-user me-1"></i> Personal
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="membership-tab" data-bs-toggle="tab" data-bs-target="#membership" type="button">
                        <i class="fas fa-id-card me-1"></i> Membership
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button">
                        <i class="fas fa-address-book me-1"></i> Contact
                    </button>
                </li>
            </ul>

            <div class="tab-content p-3" id="memberTabsContent">
                <div class="tab-pane fade show active" id="personal" role="tabpanel">

                    {{-- Primary Member Information --}}
                    <div class="card border mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-user me-1"></i> Primary Member Information
                            </h6>
                        </div>

                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Member No *</label>
                                    <input type="text"
                                        name="member_no"
                                        class="form-control"
                                        data-uppercase
                                        required
                                        value="{{ old('member_no', $member->member_no) }}">

                                    @error('member_no')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Photo</label>

                                    @if($member->photo_path)
                                        <div class="mb-2">
                                            <img src="{{ asset('storage/' . $member->photo_path) }}"
                                                alt="Photo"
                                                class="rounded"
                                                width="100">
                                        </div>
                                    @endif

                                    <input type="file" name="photo" class="form-control" accept="image/*">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">First Name *</label>
                                    <input type="text"
                                        name="first_name"
                                        class="form-control"
                                        data-uppercase
                                        required
                                        value="{{ old('first_name', $member->first_name) }}">

                                    @error('first_name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text"
                                        name="last_name"
                                        class="form-control"
                                        data-uppercase
                                        required
                                        value="{{ old('last_name', $member->last_name) }}">

                                    @error('last_name')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date"
                                        name="dateofbirth"
                                        class="form-control"
                                        value="{{ old('dateofbirth', $member->dateofbirth?->format('Y-m-d')) }}">
                                </div>

                            </div>
                        </div>
                    </div>


                    {{-- Spouse Member Information --}}
                    <div class="card border mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-user-friends me-1"></i> Spouse Member Information
                            </h6>
                        </div>

                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Spouse Member No</label>

                                    <input type="hidden"
                                        name="spouse_member_id"
                                        value="{{ old('spouse_member_id', $spouseMember->id ?? '') }}">

                                    <input type="text"
                                        name="spouse_member_no"
                                        class="form-control"
                                        data-uppercase
                                        value="{{ old('spouse_member_no', $spouseMember->member_no ?? '') }}">

                                    <small class="text-muted">
                                        Used only if spouse name is entered. You can change this number.
                                    </small>

                                    @error('spouse_member_no')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Spouse Date of Birth</label>
                                    <input type="date"
                                        name="spouse_dateofbirth"
                                        class="form-control"
                                        value="{{ old('spouse_dateofbirth', isset($spouseMember) && $spouseMember?->dateofbirth ? $spouseMember->dateofbirth->format('Y-m-d') : '') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Spouse First Name</label>
                                    <input type="text"
                                        name="spouse_first_name"
                                        class="form-control"
                                        data-uppercase
                                        value="{{ old('spouse_first_name', $spouseMember->first_name ?? $member->spouse_first_name) }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Spouse Last Name</label>
                                    <input type="text"
                                        name="spouse_last_name"
                                        class="form-control"
                                        data-uppercase
                                        value="{{ old('spouse_last_name', $spouseMember->last_name ?? $member->spouse_last_name) }}">
                                </div>

                            </div>
                        </div>
                    </div>


                    {{-- Notes --}}
                    <div class="card border">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-sticky-note me-1"></i> Notes
                            </h6>
                        </div>

                        <div class="card-body">
                            <textarea name="notes"
                                    class="form-control"
                                    rows="3">{{ old('notes', $member->notes) }}</textarea>
                        </div>
                    </div>

                </div>
                <div class="tab-pane fade" id="membership" role="tabpanel">
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Membership Type *</label>
                            <select name="membership_type_id" class="form-select" required>
                                <option value="">Select Type</option>
                                @foreach($membershipTypes as $type)
                                    <option value="{{ $type->id }}"
                                        {{ old('membership_type_id', $member->membership_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }} (${{ number_format($type->fee_amount, 2) }})
                                    </option>
                                @endforeach
                            </select>

                            @error('membership_type_id')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Receipt No</label>
                            <input type="text"
                                name="receipt_no"
                                id="receipt_no"
                                class="form-control"
                                value="{{ old('receipt_no', $member->receipt_no) }}"
                                maxlength="6">

                            <small class="text-muted">
                                Receipt number linked with this member. You can change it before updating.
                            </small>

                            @error('receipt_no')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-select" required id="status-select">
                                <option value="ACTIVE" {{ old('status', $member->status) == 'ACTIVE' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="INACTIVE" {{ old('status', $member->status) == 'INACTIVE' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3" id="status-reason-div">
                            <label class="form-label">Status Reason</label>
                            <select name="status_reason" class="form-select">
                                <option value="">Select Reason</option>
                                <option value="Out Of Town" {{ old('status_reason', $member->status_reason) == 'Out Of Town' ? 'selected' : '' }}>Out Of Town</option>
                                <option value="Passed Away" {{ old('status_reason', $member->status_reason) == 'Passed Away' ? 'selected' : '' }}>Passed Away</option>
                                <option value="Cancelled" {{ old('status_reason', $member->status_reason) == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Membership Start Date</label>
                            <input type="date"
                                name="membership_start_date"
                                class="form-control"
                                value="{{ old('membership_start_date', optional($member->membership_start_date)->format('Y-m-d')) }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Mode *</label>
                            <select name="payment_mode"
                                    id="payment_mode"
                                    class="form-select"
                                    required>
                                <option value="CASH"
                                    {{ old('payment_mode', $receipt->payment_mode ?? 'CASH') == 'CASH' ? 'selected' : '' }}>
                                    Cash
                                </option>

                                <option value="CHECK"
                                    {{ old('payment_mode', $receipt->payment_mode ?? '') == 'CHECK' ? 'selected' : '' }}>
                                    Check
                                </option>

                                <option value="CREDIT_CARD"
                                    {{ old('payment_mode', $receipt->payment_mode ?? '') == 'CREDIT_CARD' ? 'selected' : '' }}>
                                    Credit Card
                                </option>
                            </select>

                            @error('payment_mode')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3 check-fields">
                            <label class="form-label">Bank Name</label>
                            <input type="text"
                                name="bank_name"
                                class="form-control"
                                value="{{ old('bank_name', $receipt->bank_name ?? '') }}"
                                maxlength="255">

                            @error('bank_name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3 check-fields">
                            <label class="form-label">Check Number</label>
                            <input type="text"
                                name="check_number"
                                class="form-control"
                                value="{{ old('check_number', $receipt->check_number ?? '') }}"
                                maxlength="255">

                            @error('check_number')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3 check-fields">
                            <label class="form-label">Check Date</label>
                            <input type="date"
                                name="check_date"
                                class="form-control"
                                value="{{ old('check_date', optional($receipt->check_date ?? null)->format('Y-m-d')) }}">

                            @error('check_date')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                <div class="tab-pane fade" id="contact" role="tabpanel">

                    {{-- Primary Contact Information --}}
                    <div class="card border mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-address-card me-1"></i> Primary Contact Information
                            </h6>
                        </div>

                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email"
                                        name="email"
                                        class="form-control"
                                        value="{{ old('email', $member->email) }}">

                                    @error('email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="text"
                                        name="phone"
                                        class="form-control"
                                        value="{{ old('phone', $member->phone) }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cell Phone</label>
                                    <input type="text"
                                        name="cell_phone"
                                        class="form-control"
                                        value="{{ old('cell_phone', $member->cell_phone) }}">
                                </div>

                            </div>
                        </div>
                    </div>


                    {{-- Spouse Contact Information --}}
                    <div class="card border mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-user-friends me-1"></i> Spouse Contact Information
                            </h6>
                        </div>

                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Spouse Email</label>
                                    <input type="email"
                                        name="spouse_email"
                                        class="form-control"
                                        value="{{ old('spouse_email', $spouseMember->email ?? '') }}">

                                    @error('spouse_email')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Spouse Cell Phone</label>
                                    <input type="text"
                                        name="spouse_cell_phone"
                                        class="form-control"
                                        value="{{ old('spouse_cell_phone', $spouseMember->cell_phone ?? '') }}">
                                </div>

                            </div>
                        </div>
                    </div>


                    {{-- Shared Address Information --}}
                    <div class="card border">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-home me-1"></i> Shared Address Information
                            </h6>
                        </div>

                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Address 1</label>
                                    <input type="text"
                                        name="address1"
                                        class="form-control"
                                        value="{{ old('address1', $member->address1) }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Address 2</label>
                                    <input type="text"
                                        name="address2"
                                        class="form-control"
                                        value="{{ old('address2', $member->address2) }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City</label>
                                    <input type="text"
                                        name="city"
                                        class="form-control"
                                        value="{{ old('city', $member->city) }}">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">State</label>
                                    <input type="text"
                                        name="state"
                                        class="form-control"
                                        value="{{ old('state', $member->state) }}">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Zip</label>
                                    <input type="text"
                                        name="zip"
                                        class="form-control"
                                        value="{{ old('zip', $member->zip) }}">
                                </div>

                                <div class="col-md-2 mb-3">
                                    <label class="form-label">County</label>
                                    <input type="text"
                                        name="county"
                                        class="form-control"
                                        value="{{ old('county', $member->county) }}">
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.members.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Member</button>
            </div>
        </form>
    </div>
</div>
@endsection
<style>
    #memberTabsContent .card-header h6 {
        font-weight: 600;
        color: #1f3b64;
    }

    #memberTabsContent .card {
        box-shadow: none;
        border-color: #d9e2ef !important;
    }

    #memberTabsContent .card-header {
        padding: 0.65rem 1rem;
        border-bottom: 1px solid #d9e2ef;
    }

    #memberTabsContent .card-body {
        padding-bottom: 0.5rem;
    }
</style>

@section('scripts')
<script>
$(document).ready(function() {

    function toggleStatusReason() {
        var status = $('#status-select').val();

        if (status === 'INACTIVE') {
            $('#status-reason-div').show();
        } else {
            $('#status-reason-div').hide();
            $('#status-reason-div select').val('');
        }
    }

    $('#status-select').change(toggleStatusReason);
    toggleStatusReason();

    /*
    |--------------------------------------------------------------------------
    | Show check fields only when payment mode is CHECK
    |--------------------------------------------------------------------------
    */
    function toggleCheckFields() {
        var paymentMode = $('#payment_mode').val();

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
@endsection
