@extends('admin.admin_dashboard')

@section('title', 'Add Payment for ' . $member->full_name)
@section('page-title', 'Add Payment')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="mb-3 p-3 bg-light rounded">
            <strong>Member:</strong> {{ $member->full_name }} ({{ $member->member_no }})
        </div>
        
        <form action="{{ route('admin.members.payments.store', $member->id) }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Payment Date *</label>
                    <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                    @error('payment_date')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Amount *</label>
                    <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required>
                    @error('amount')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Method *</label>
                    <select name="method" class="form-select" required>
                        <option value="CASH">Cash</option>
                        <option value="CHECK">Check</option>
                        <option value="CARD">Card</option>
                        <option value="OTHER">Other</option>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Receipt No</label>
                    <input type="text" name="receipt_no" class="form-control">
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Remarks</label>
                    <input type="text" name="remarks" class="form-control">
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.members.show', $member->id) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection
