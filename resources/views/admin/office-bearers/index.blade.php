@extends('admin.admin_dashboard')

@section('title', 'Office Bearers')
@section('page-title', 'Office Bearers')

@section('page-actions')
<a href="{{ route('admin.office-bearers.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Add Office Bearer
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <table id="office-bearers-table" class="table table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
                <tr>
                    <th>Position</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Order</th>
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
        $('#office-bearers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.office-bearers.ajax") }}',
                type: 'GET'
            },
            columns: [
                { data: 'position', name: 'position' },
                { data: 'name', name: 'name' },
                { data: 'phone', name: 'phone' },
                { data: 'display_order', name: 'display_order' },
                { 
                    data: 'id', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function(id) {
                        return '<a href="/office-bearers/' + id + '/edit" class="btn btn-sm btn-primary me-1"><i class="fas fa-edit"></i></a>' +
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deleteItem(' + id + ')"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });
    });
    
    function deleteItem(id) {
        deleteItem('{{ route("admin.office-bearers.destroy", ":id") }}'.replace(':id', id), id);
    }
</script>
@endsection
