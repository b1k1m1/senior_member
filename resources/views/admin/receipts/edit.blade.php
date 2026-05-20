@extends('admin.admin_dashboard')

@section('title', 'Edit Receipt')
@section('page-title', 'Edit Receipt')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.receipts.update', $receipt->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <ul class="nav nav-tabs" id="receiptTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button">Basic Info</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="membership-tab" data-bs-toggle="tab" data-bs-target="#membership" type="button">Membership Details</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="event-tab" data-bs-toggle="tab" data-bs-target="#event" type="button">Event Details</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="donation-tab" data-bs-toggle="tab" data-bs-target="#donation" type="button">Donation</button>
                </li>
            </ul>
            
            <div class="tab-content p-3" id="receiptTabsContent">
                <!-- Basic Info Tab -->
                <div class="tab-pane fade show active" id="basic">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Receipt Type *</label>
                            <select name="receipt_type_id" id="receipt_type_id" class="form-select" required>
                                <option value="">Select Type</option>
                                @foreach($receiptTypes as $type)
                                <option value="{{ $type->id }}" data-code="{{ $type->code }}" {{ old('receipt_type_id', $receipt->receipt_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Received From *</label>
                            <input type="text" name="received_from" class="form-control" required value="{{ old('received_from', $receipt->received_from) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address1" class="form-control" value="{{ old('address1', $receipt->address1) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address2" class="form-control" value="{{ old('address2', $receipt->address2) }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" value="{{ old('city', $receipt->city) }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control" value="{{ old('state', $receipt->state) }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">ZIP</label>
                            <input type="text" name="zip" class="form-control" value="{{ old('zip', $receipt->zip) }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">County</label>
                            <input type="text" name="county" class="form-control" value="{{ old('county', $receipt->county) }}">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Mode *</label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="CASH" {{ $receipt->payment_mode == 'CASH' ? 'selected' : '' }}>Cash</option>
                                <option value="CHECK" {{ $receipt->payment_mode == 'CHECK' ? 'selected' : '' }}>Check</option>
                                <option value="CREDIT_CARD" {{ $receipt->payment_mode == 'CREDIT_CARD' ? 'selected' : '' }}>Credit Card</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $receipt->bank_name) }}">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Check Number</label>
                            <input type="text" name="check_number" class="form-control" value="{{ old('check_number', $receipt->check_number) }}">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Check Date</label>
                            <input type="date" name="check_date" class="form-control" value="{{ old('check_date', $receipt->check_date?->format('Y-m-d')) }}">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount *</label>
                            <input type="number" name="amount" class="form-control" required min="0" step="0.01" value="{{ old('amount', $receipt->amount) }}">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="1">{{ old('remarks', $receipt->remarks) }}</textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Membership Details Tab -->
                <div class="tab-pane fade" id="membership">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Membership Type</label>
                            <select name="membership_type_id" class="form-select">
                                <option value="">Select Membership Type</option>
                                @foreach($membershipTypes as $type)
                                <option value="{{ $type->id }}" {{ old('membership_type_id', $receipt->membership_type_id) == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="has_spouse" class="form-check-input" id="has_spouse" value="1" {{ old('has_spouse', $receipt->has_spouse) ? 'checked' : '' }}>
                                <label class="form-check-label" for="has_spouse">Include Spouse (Life Membership for Couple)</label>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3"><hr><h6>Primary Member</h6></div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Member First Name *</label>
                            <input type="text" name="member_first_name" class="form-control" value="{{ old('member_first_name', $receipt->member?->first_name) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Member Last Name *</label>
                            <input type="text" name="member_last_name" class="form-control" value="{{ old('member_last_name', $receipt->member?->last_name) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="member_dateofbirth" class="form-control" value="{{ old('member_dateofbirth', $receipt->member?->dateofbirth?->format('Y-m-d')) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="member_email" class="form-control" value="{{ old('member_email', $receipt->member?->email) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="member_phone" class="form-control" value="{{ old('member_phone', $receipt->member?->phone) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cell Phone</label>
                            <input type="text" name="member_cell_phone" class="form-control" value="{{ old('member_cell_phone', $receipt->member?->cell_phone) }}">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="member_address1" class="form-control mb-2" placeholder="Address Line 1" value="{{ old('member_address1', $receipt->member?->address1) }}">
                            <input type="text" name="member_address2" class="form-control mb-2" placeholder="Address Line 2" value="{{ old('member_address2', $receipt->member?->address2) }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="member_city" class="form-control" value="{{ old('member_city', $receipt->member?->city) }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="member_state" class="form-control" value="{{ old('member_state', $receipt->member?->state) }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">ZIP</label>
                            <input type="text" name="member_zip" class="form-control" value="{{ old('member_zip', $receipt->member?->zip) }}">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">County</label>
                            <input type="text" name="member_county" class="form-control" value="{{ old('member_county', $receipt->member?->county) }}">
                        </div>
                        
                        <div class="col-12 mb-3"><hr><h6>Spouse Details</h6></div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse First Name</label>
                            <input type="text" name="member_spouse_first_name" class="form-control" value="{{ old('member_spouse_first_name', $spouse?->first_name) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Last Name</label>
                            <input type="text" name="member_spouse_last_name" class="form-control" value="{{ old('member_spouse_last_name', $spouse?->last_name) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Date of Birth</label>
                            <input type="date" name="member_spouse_dateofbirth" class="form-control" value="{{ old('member_spouse_dateofbirth', $spouse?->dateofbirth?->format('Y-m-d')) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Cell Phone</label>
                            <input type="text" name="member_spouse_cell_phone" class="form-control" value="{{ old('member_spouse_cell_phone', $spouse?->cell_phone) }}">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Email</label>
                            <input type="email" name="member_spouse_email" class="form-control" value="{{ old('member_spouse_email', $spouse?->email) }}">
                        </div>
                    </div>
                </div>
                
                <!-- Event Details Tab -->
                <div class="tab-pane fade" id="event">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Select Event</label>
                            <select name="event_id" class="form-select">
                                <option value="">Select Event</option>
                                @foreach($events as $event)
                                <option value="{{ $event->id }}" {{ old('event_id', $receipt->event_id) == $event->id ? 'selected' : '' }}>{{ $event->title }} ({{ $event->start_date->format('M j, Y') }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Donation Tab -->
                <div class="tab-pane fade" id="donation">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Donor Name (if different from Received From)</label>
                            <input type="text" name="donor_name" class="form-control" value="{{ old('donor_name', $receipt->donor_name) }}" placeholder="Enter donor name">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('admin.receipts.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Receipt</button>
            </div>
        </form>
    </div>
</div>
@endsection
