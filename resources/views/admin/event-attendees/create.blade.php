@extends('admin.admin_dashboard')

@section('title', 'Add Attendee')
@section('page-title', 'Add Attendee - ' . $event->title)

@section('page-actions')
<a href="{{ route('admin.event-attendees.index', ['event' => $event->id]) }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left me-1"></i> Back
</a>
@endsection

@section('styles')
<style>
.select2-container { width: 100% !important; }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.event-attendees.store', $event->id) }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Member *</label>
                    <select name="member_id" id="member-select" class="form-select" required>
                        <option value="">Search Member...</option>
                    </select>
                    @error('member_id')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="TENTATIVE" {{ old('status') == 'TENTATIVE' ? 'selected' : '' }}>Tentative</option>
                        <option value="CONFIRMED" {{ old('status') == 'CONFIRMED' ? 'selected' : '' }}>Confirmed</option>
                        <option value="CANCELLED" {{ old('status') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                
                @if($event->attendance_type == 'MEMBERS_WITH_GUESTS')
                <div class="col-md-6 mb-3">
                    <label class="form-label">Number of Guests</label>
                    <input type="number" name="guests_count" class="form-control" min="0" max="{{ $event->max_guests_per_member ?? '' }}" value="{{ old('guests_count', 0) }}">
                </div>
                @endif
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Amount Paid</label>
                    <input type="number" name="amount_paid" class="form-control" min="0" step="0.01" value="{{ old('amount_paid', 0) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date') }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Receipt No</label>
                    <input type="text" name="receipt_no" class="form-control" value="{{ old('receipt_no') }}">
                </div>
                
                <div class="col-md-12 mb-3">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('admin.event-attendees.index', ['event' => $event->id]) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#member-select').select2({
        ajax: {
            url: '{{ route("admin.members.search") }}',
            dataType: 'json',
            delay: 250,
            processResults: function(data) {
                return {
                    results: data.results
                };
            },
            cache: true
        },
        minimumInputLength: 2,
        placeholder: 'Search by name, member no, or email',
        allowClear: true
    });
});
</script>
@endsection
