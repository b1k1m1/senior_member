@extends('admin.admin_dashboard')

@section('title', 'Events')
@section('page-title', 'Events')

@section('page-actions')
<a href="{{ route('admin.events.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Add Event
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <table id="events-table" class="table table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Capacity</th>
                    <th>Confirmed</th>
                    <th>Tentative</th>
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
        $('#events-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.events.ajax") }}',
                type: 'GET'
            },
            columns: [
                { data: 'title', name: 'title' },
                { data: 'event_type', name: 'eventType.name' },
                { data: 'start_date', name: 'start_date' },
                { data: 'end_date', name: 'end_date' },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data) {
                        var badges = {
                            'ACTIVE': 'bg-success',
                            'SCHEDULED': 'bg-primary',
                            'RESCHEDULED': 'bg-warning',
                            'CANCELLED': 'bg-danger',
                            'COMPLETED': 'bg-secondary'
                        };
                        return '<span class="badge ' + (badges[data] || 'bg-secondary') + '">' + data + '</span>';
                    }
                },
                { data: 'capacity', name: 'capacity' },
                { data: 'confirmed', name: 'confirmed' },
                { data: 'tentative', name: 'tentative' },
                { 
                    data: 'id', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function(id) {
                        return '<a href="/events/' + id + '" class="btn btn-sm btn-info me-1"><i class="fas fa-eye"></i></a>' +
                               '<a href="/events/' + id + '/edit" class="btn btn-sm btn-primary me-1"><i class="fas fa-edit"></i></a>' +
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deleteEvent(' + id + ')"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });
    });
    
    function deleteEvent(id) {
        deleteItem('{{ route("admin.events.destroy", ":id") }}'.replace(':id', id), id);
    }
</script>
@endsection
