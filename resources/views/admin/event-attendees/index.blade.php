@extends('admin.admin_dashboard')

@section('title', 'Event Attendees')
@section('page-title', 'Attendees - ' . $event->title)

@section('page-actions')
<a href="{{ route('admin.events.index') }}" class="btn btn-secondary me-2">
    <i class="fas fa-arrow-left me-1"></i> Back to Events
</a>
<a href="{{ route('admin.event-attendees.create', ['event' => $event->id]) }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Add Attendee
</a>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="text-muted small">Event Date</div>
                <strong>{{ $event->start_date->format('F j, Y') }} - {{ $event->end_date->format('F j, Y') }}</strong>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Status</div>
                <span class="badge bg-primary">{{ $event->status }}</span>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Confirmed / Tentative</div>
                <strong>{{ $event->confirmed_count }} / {{ $event->tentative_count }}</strong>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Capacity</div>
                <strong>{{ $event->capacity ? $event->capacity . ' / ' . $event->available_slacks : 'Unlimited' }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <table id="attendees-table" class="table table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
                <tr>
                    <th>Member No</th>
                    <th>Member Name</th>
                    <th>Status</th>
                    <th>Guests</th>
                    <th>Amount Paid</th>
                    <th>Payment Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        $('#attendees-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.event-attendees.ajax", ["event" => $event->id]) }}',
                type: 'GET'
            },
            columns: [
                { data: 'member_no', name: 'member_no' },
                { data: 'member_name', name: 'member_name' },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        var badges = {
                            'CONFIRMED': 'bg-success',
                            'TENTATIVE': 'bg-warning',
                            'CANCELLED': 'bg-danger'
                        };
                        return '<span class="badge ' + (badges[data] || 'bg-secondary') + '">' + data + '</span>';
                    }
                },
                { data: 'guests_count', name: 'guests_count' },
                { data: 'amount_paid', name: 'amount_paid', render: function(data) { return data ? '$' + parseFloat(data).toFixed(2) : '-'; } },
                { data: 'payment_date', name: 'payment_date' },
                { 
                    data: 'id', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function(id) {
                        return '<button type="button" class="btn btn-sm btn-danger" onclick="deleteAttendee(' + id + ')"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });
    });
    
    function deleteAttendee(id) {
        deleteItem('{{ route("admin.event-attendees.destroy", ["event" => $event->id, "attendee" => ":id"]) }}'.replace(':id', id), id);
    }
</script>
@endsection
