@extends('admin.admin_dashboard')

@section('title', 'Roles')
@section('page-title', 'Roles')

@section('page-actions')
<a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Add Role
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <table id="roles-table" class="table table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Permissions Count</th>
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
        $('#roles-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.roles.ajax") }}',
                type: 'GET'
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'permissions', name: 'permissions' },
                { data: 'created_at', name: 'created_at' },
                { 
                    data: 'id', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function(id, type, row) {
                        var actions = '<a href="/roles/' + id + '/edit" class="btn btn-sm btn-primary me-1"><i class="fas fa-edit"></i></a>';
                        if (row.name !== 'Super Admin') {
                            actions += '<button type="button" class="btn btn-sm btn-danger" onclick="deleteRole(' + id + ')"><i class="fas fa-trash"></i></button>';
                        }
                        return actions;
                    }
                }
            ],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });
    });
    
    function deleteRole(id) {
        deleteItem('{{ route("admin.roles.index") }}/' + id, id);
    }
</script>
@endsection
