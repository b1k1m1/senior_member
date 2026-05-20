@extends('admin.admin_dashboard')

@section('title', 'Organizations')
@section('page-title', 'Organizations')

@section('page-actions')
<a href="{{ route('admin.organizations.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Add Organization
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <table id="organizations-table" class="table table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Email</th>
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
        $('#organizations-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.organizations.ajax") }}',
                type: 'GET'
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'phone', name: 'phone' },
                { data: 'email', name: 'email' },
                { 
                    data: 'id', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function(id) {
                        return '<a href="/organizations/' + id + '/edit" class="btn btn-sm btn-primary me-1"><i class="fas fa-edit"></i></a>' +
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deleteItem(' + id + ')"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });
    });
    
    function deleteItem(id) {
        deleteItem('{{ route("admin.organizations.destroy", ":id") }}'.replace(':id', id), id);
    }
</script>
@endsection
