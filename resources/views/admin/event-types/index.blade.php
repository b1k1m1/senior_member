@extends('admin.admin_dashboard')

@section('title', 'Event Types')
@section('page-title', 'Event Types')

@section('page-actions')
<a href="{{ route('admin.event-types.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Add Event Type
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <table id="event-types-table" class="table table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Created At</th>
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
        $('#event-types-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.event-types.ajax") }}',
                type: 'GET'
            },
            columns: [
                { data: 'name', name: 'name' },
                { 
                    data: 'is_active', 
                    name: 'is_active',
                    render: function(data) {
                        return data 
                            ? '<span class="badge bg-success">Active</span>' 
                            : '<span class="badge bg-secondary">Inactive</span>';
                    }
                },
                { data: 'created_at', name: 'created_at' },
                { 
                    data: 'id', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function(id) {
                        return '<a href="/event-types/' + id + '/edit" class="btn btn-sm btn-primary me-1"><i class="fas fa-edit"></i></a>' +
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deleteEventType(' + id + ')"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });
    });
    
    function deleteEventType(id) {
        deleteItem('{{ route("admin.event-types.destroy", ":id") }}'.replace(':id', id), id);
    }
</script>
@endsection
