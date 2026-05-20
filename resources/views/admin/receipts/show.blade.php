@extends('admin.admin_dashboard')

@section('title', 'Receipt Details')
@section('page-title', 'Receipt Details')

@section('page-actions')
<a href="{{ route('admin.receipts.print', $receipt->id) }}" class="btn btn-primary" target="_blank">
    <i class="fas fa-print me-1"></i> Print
</a>
<a href="{{ route('admin.receipts.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-1"></i> Back
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Receipt Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td width="30%" class="text-muted">Receipt No:</td>
                        <td><strong>{{ $receipt->receipt_no }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Type:</td>
                        <td><span class="badge bg-primary">{{ $receipt->receiptType?->name }}</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Received From:</td>
                        <td>{{ $receipt->received_from }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Address:</td>
                        <td>
                            {{ $receipt->address1 }}<br>
                            {{ $receipt->address2 ? $receipt->address2 . '<br>' : '' }}
                            {{ $receipt->city }}, {{ $receipt->state }} {{ $receipt->zip }}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Payment Mode:</td>
                        <td>{{ $receipt->payment_mode }}</td>
                    </tr>
                    @if($receipt->bank_name)
                    <tr>
                        <td class="text-muted">Bank:</td>
                        <td>{{ $receipt->bank_name }}</td>
                    </tr>
                    @endif
                    @if($receipt->check_number)
                    <tr>
                        <td class="text-muted">Check No:</td>
                        <td>{{ $receipt->check_number }} ({{ $receipt->check_date?->format('M j, Y') }})</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Amount:</td>
                        <td><strong>${{ number_format($receipt->amount, 2) }}</strong></td>
                    </tr>
                    @if($receipt->remarks)
                    <tr>
                        <td class="text-muted">Remarks:</td>
                        <td>{{ $receipt->remarks }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Date:</td>
                        <td>{{ $receipt->created_at->format('F j, Y g:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        @if($receipt->member)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Member Details</h5>
            </div>
            <div class="card-body">
                <p><strong>{{ $receipt->member->first_name }} {{ $receipt->member->last_name }}</strong></p>
                <p class="text-muted">Member No: {{ $receipt->member->member_no }}</p>
                <p class="text-muted">{{ $receipt->membershipType?->name }}</p>
            </div>
        </div>
        @endif
        
        @if($receipt->event)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Event Details</h5>
            </div>
            <div class="card-body">
                <p><strong>{{ $receipt->event->title }}</strong></p>
                <p class="text-muted">{{ $receipt->event->start_date->format('F j, Y') }}</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
