@extends('admin.admin_dashboard')

@section('title', 'Users')
@section('page-title', 'Users')

@section('page-actions')
<a href="{{ route('admin.users.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Add User
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <table id="users-table" class="table table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Roles</th>
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
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.users.ajax") }}',
                type: 'GET'
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'username', name: 'username' },
                { data: 'email', name: 'email' },
                { data: 'roles', name: 'roles' },
                { data: 'created_at', name: 'created_at' },
                { 
                    data: 'id', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function(id) {
                        return '<a href="/users/' + id + '/edit" class="btn btn-sm btn-primary me-1"><i class="fas fa-edit"></i></a>' +
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deleteUser(' + id + ')"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });
    });
    
    function deleteUser(id) {
        deleteItem('{{ route("admin.users.index") }}/' + id, id);
    }
</script>
@endsection
