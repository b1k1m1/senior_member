@extends('admin.admin_dashboard')

@section('title', 'New Receipt')
@section('page-title', 'New Receipt')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.receipts.store') }}" method="POST">
            @csrf
            
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
                                <option value="{{ $type->id }}" data-code="{{ $type->code }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Received From *</label>
                            <input type="text" name="received_from" class="form-control" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="address1" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="address2" class="form-control">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-control">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">ZIP</label>
                            <input type="text" name="zip" class="form-control">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">County</label>
                            <input type="text" name="county" class="form-control">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Mode *</label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="CASH">Cash</option>
                                <option value="CHECK">Check</option>
                                <option value="CREDIT_CARD">Credit Card</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Check Number</label>
                            <input type="text" name="check_number" class="form-control">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Check Date</label>
                            <input type="date" name="check_date" class="form-control">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount *</label>
                            <input type="number" name="amount" class="form-control" required min="0" step="0.01">
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="1"></textarea>
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
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="has_spouse" class="form-check-input" id="has_spouse" value="1">
                                <label class="form-check-label" for="has_spouse">Include Spouse (Life Membership for Couple)</label>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3"><hr><h6>Primary Member</h6></div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Member First Name *</label>
                            <input type="text" name="member_first_name" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Member Last Name *</label>
                            <input type="text" name="member_last_name" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="member_dateofbirth" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="member_email" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="member_phone" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cell Phone</label>
                            <input type="text" name="member_cell_phone" class="form-control">
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="member_address1" class="form-control mb-2" placeholder="Address Line 1">
                            <input type="text" name="member_address2" class="form-control mb-2" placeholder="Address Line 2">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">City</label>
                            <input type="text" name="member_city" class="form-control">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">State</label>
                            <input type="text" name="member_state" class="form-control">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">ZIP</label>
                            <input type="text" name="member_zip" class="form-control">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">County</label>
                            <input type="text" name="member_county" class="form-control">
                        </div>
                        
                        <div class="col-12 mb-3"><hr><h6>Spouse Details</h6></div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse First Name</label>
                            <input type="text" name="member_spouse_first_name" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Last Name</label>
                            <input type="text" name="member_spouse_last_name" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Date of Birth</label>
                            <input type="date" name="member_spouse_dateofbirth" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Cell Phone</label>
                            <input type="text" name="member_spouse_cell_phone" class="form-control">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Spouse Email</label>
                            <input type="email" name="member_spouse_email" class="form-control">
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
                                <option value="{{ $event->id }}">{{ $event->title }} ({{ $event->start_date->format('M j, Y') }})</option>
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
                            <input type="text" name="donor_name" class="form-control" placeholder="Enter donor name">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('admin.receipts.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Receipt</button>
            </div>
        </form>
    </div>
</div>
@endsection
