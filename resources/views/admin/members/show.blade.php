@extends('admin.admin_dashboard')

@section('title', 'Member Details')
@section('page-title', 'Member Details')

@section('page-actions')
<div class="d-flex gap-2">
    @can('members.edit')
    <a href="{{ route('admin.members.edit', $member->id) }}" class="btn btn-primary">
        <i class="fas fa-edit me-1"></i> Edit
    </a>
    @endcan
    <a href="{{ route('admin.members.welcome-letter', $member->id) }}" class="btn btn-info" target="_blank">
        <i class="fas fa-envelope me-1"></i> Welcome Letter
    </a>
    @can('payments.manage')
    <a href="{{ route('admin.members.payments.create', $member->id) }}" class="btn btn-success">
        <i class="fas fa-plus me-1"></i> Add Payment
    </a>
    @endcan
    <a href="{{ route('admin.members.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @if($member->photo_path)
                <img src="{{ asset('storage/' . $member->photo_path) }}" alt="Photo" class="rounded mb-3" width="150" height="150">
                @else
                <div class="bg-secondary d-inline-flex align-items-center justify-content-center rounded mb-3" style="width:150px;height:150px;">
                    <i class="fas fa-user text-white" style="font-size: 4rem;"></i>
                </div>
                @endif
                
                <h4>{{ $member->full_name }}</h4>
                <p class="text-muted">{{ $member->member_no }}</p>
                
                @if($member->status === 'ACTIVE')
                <span class="badge bg-success">Active</span>
                @else
                <span class="badge bg-secondary">Inactive</span>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <i class="fas fa-id-card me-1"></i> Membership Info
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted">Type:</td>
                        <td>{{ $member->membershipType?->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Start Date:</td>
                        <td>{{ $member->membership_start_date?->format('m/d/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Joining Year:</td>
                        <td>{{ $member->joining_year }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Receipt No:</td>
                        <td>{{ $member->receipt_no ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user me-1"></i> Personal Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Spouse:</strong> {{ $member->spouse_first_name }} {{ $member->spouse_last_name ?: 'N/A' }}</p>
                        <p class="mb-2"><strong>Email:</strong> {{ $member->email ?: 'N/A' }}</p>
                        <p class="mb-2"><strong>Phone:</strong> {{ $member->phone ?: 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-2"><strong>Address:</strong></p>
                        <p class="text-muted">
                            {{ $member->address1 }}<br>
                            {{ $member->address2 ? $member->address2 . '<br>' : '' }}
                            {{ $member->city }}, {{ $member->state }} {{ $member->zip }}
                        </p>
                    </div>
                </div>
                
                @if($member->notes)
                <hr>
                <p class="mb-0"><strong>Notes:</strong></p>
                <p class="text-muted mb-0">{{ $member->notes }}</p>
                @endif
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-money-bill-wave me-1"></i> Payment History</span>
                <span class="badge bg-primary">Total: ${{ number_format($member->total_payments, 2) }}</span>
            </div>
            <div class="card-body p-0">
                @if($member->payments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Receipt No</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($member->payments as $payment)
                            <tr>
                                <td>{{ $payment->payment_date->format('m/d/Y') }}</td>
                                <td>${{ number_format($payment->amount, 2) }}</td>
                                <td><span class="badge bg-info">{{ $payment->method }}</span></td>
                                <td>
                                    {{ $payment->receipt_no ?? 'N/A' }}
                                    <a href="{{ route('admin.payments.receipt', $payment->id) }}" class="btn btn-sm btn-outline-primary ms-1" target="_blank" title="Download Receipt">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </td>
                                <td>{{ $payment->remarks ?? 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center text-muted py-4">
                    No payments recorded yet.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
