@extends('admin.admin_dashboard')

@section('title', 'Add Member')
@section('page-title', 'Add Member')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.members.store') }}" method="POST" enctype="multipart/form-data" id="member-form">
            @csrf

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
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Member No *</label>
                            <input type="text"
                                name="member_no"
                                class="form-control"
                                data-uppercase
                                required
                                value="{{ old('member_no', $nextMemberNo ?? '') }}">
                                <small class="text-muted">
                                    Suggested next member number. You can change it before saving.
                                </small>
                            @error('member_no')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-control" accept="image/*">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" data-uppercase required value="{{ old('first_name') }}">
                            @error('first_name')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" data-uppercase required value="{{ old('last_name') }}">
                            @error('last_name')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="dateofbirth" class="form-control" value="{{ old('dateofbirth') }}">
                        </div>
                                                <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Member No</label>
                            <input type="text"
                                name="spouse_member_no"
                                class="form-control"
                                data-uppercase
                                value="{{ old('spouse_member_no', $nextSpouseMemberNo ?? '') }}">
                            <small class="text-muted">
                                Used only if spouse name is entered. You can change this number before saving.
                            </small>
                            @error('spouse_member_no')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse First Name</label>
                            <input type="text"
                                name="spouse_first_name"
                                class="form-control"
                                data-uppercase
                                value="{{ old('spouse_first_name') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Last Name</label>
                            <input type="text"
                                name="spouse_last_name"
                                class="form-control"
                                data-uppercase
                                value="{{ old('spouse_last_name') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Date of Birth</label>
                            <input type="date"
                                name="spouse_dateofbirth"
                                class="form-control"
                                value="{{ old('spouse_dateofbirth') }}">
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
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
                                <option value="{{ $type->id }}" {{ old('membership_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} (${{ number_format($type->fee_amount, 2) }})
                                </option>
                                @endforeach
                            </select>
                            @error('membership_type_id')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-select" required id="status-select">
                                <option value="ACTIVE" {{ old('status') == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                                <option value="INACTIVE" {{ old('status') == 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3" id="status-reason-div" style="display: none;">
                            <label class="form-label">Status Reason</label>
                            <select name="status_reason" class="form-select">
                                <option value="">Select Reason</option>
                                <option value="Out Of Town" {{ old('status_reason') == 'Out Of Town' ? 'selected' : '' }}>Out Of Town</option>
                                <option value="Passed Away" {{ old('status_reason') == 'Passed Away' ? 'selected' : '' }}>Passed Away</option>
                                <option value="Cancelled" {{ old('status_reason') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Membership Start Date</label>
                            <input type="date" name="membership_start_date" class="form-control" value="{{ old('membership_start_date') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Receipt No</label>
                            <input type="text" name="receipt_no" class="form-control" value="{{ old('receipt_no') }}">
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="contact" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                            @error('email')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cell Phone</label>
                            <input type="text" name="cell_phone" class="form-control" value="{{ old('cell_phone') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Email</label>
                            <input type="email"
                                name="spouse_email"
                                class="form-control"
                                value="{{ old('spouse_email') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Cell Phone</label>
                            <input type="text"
                                name="spouse_cell_phone"
                                class="form-control"
                                value="{{ old('spouse_cell_phone') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address 1</label>
                            <input type="text" name="address1" class="form-control" value="{{ old('address1') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address 2</label>
                            <input type="text" name="address2" class="form-control" value="{{ old('address2') }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">Zip</label>
                            <input type="text" name="zip" class="form-control" value="{{ old('zip') }}">
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="form-label">County</label>
                            <input type="text" name="county" class="form-control" value="{{ old('county') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.members.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Member</button>
            </div>
        </form>
    </div>
</div>
@endsection

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
});
</script>
@endsection
