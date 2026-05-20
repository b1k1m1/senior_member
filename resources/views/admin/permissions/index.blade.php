@extends('admin.admin_dashboard')

@section('title', 'Permissions')
@section('page-title', 'Permissions')

@section('page-actions')
<a href="{{ route('admin.permissions.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Add Permission
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <table id="permissions-table" class="table table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Guard Name</th>
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
        $('#permissions-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.permissions.ajax") }}',
                type: 'GET'
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'guard_name', name: 'guard_name' },
                { data: 'created_at', name: 'created_at' },
                { 
                    data: 'id', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function(id) {
                        return '<a href="/permissions/' + id + '/edit" class="btn btn-sm btn-primary me-1"><i class="fas fa-edit"></i></a>' +
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deletePermission(' + id + ')"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });
    });
    
    function deletePermission(id) {
        deleteItem('{{ route("admin.permissions.index") }}/' + id, id);
    }
</script>
@endsection
