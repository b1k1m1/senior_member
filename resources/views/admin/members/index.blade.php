@extends('admin.admin_dashboard')

@section('title', 'Members')
@section('page-title', 'Members')

@section('page-actions')
@can('members.create')
<a href="{{ route('admin.members.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Add Member
</a>
<a href="{{ route('admin.members.import.form') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Import from Excel Member
</a>
@endcan
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-2">
                <select id="search-column" class="form-select">
                    <option value="all">All Fields</option>
                    <option value="member_no">Member No</option>
                    <option value="last_name">Last Name</option>
                    <option value="first_name">First Name</option>
                    <option value="phone">Phone</option>
                    <option value="city">City</option>
                    <option value="joining_year">Joining Year</option>
                    <option value="status">Status</option>
                    <option value="receipt_no">Receipt No</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" id="search-value" class="form-control" placeholder="Search...">
            </div>
            <div class="col-md-2">
                <button id="btn-search" class="btn btn-primary">Search</button>
                <button id="btn-reset" class="btn btn-secondary">Reset</button>
            </div>
        </div>
        <table id="members-table" class="table table-sm table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Member No</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                    <th>City</th>
                    <th>Joining Year</th>
                    <th>Status</th>
                    <th>Receipt No</th>
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
    var dataTable;

    $(function() {
        dataTable = $('#members-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.members.ajax', [], false) }}",
                type: 'GET',
                data: function(d) {
                    d.search_column = $('#search-column').val();
                    d.search_value = $('#search-value').val();
                }
            },
            columns: [
                {
                    data: 'photo_path',
                    name: 'photo',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (data) {
                            return '<img src="/storage/' + data + '" class="rounded-circle" width="30" height="30" alt="Photo">';
                        }
                        return '<div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width:30px;height:30px;"><i class="text-white fas fa-user"></i></div>';
                    }
                },
                { data: 'member_no', name: 'member_no' },
                { data: 'first_name', name: 'first_name' },
                { data: 'last_name', name: 'last_name' },
                { data: 'phone', name: 'phone' },
                { data: 'city', name: 'city' },
                { data: 'joining_year', name: 'joining_year' },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data) {
                        return data === 'ACTIVE'
                            ? '<span class="badge bg-success">Active</span>'
                            : '<span class="badge bg-secondary">Inactive</span>';
                    }
                },
                { data: 'receipt_no', name: 'receipt_no' },
                {
                    data: 'id',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    render: function(id, type, row) {
                        var actions = '';

                        @can('members.view')
                        actions += '<a href="/members/' + id + '" class="btn btn-sm btn-info me-1" title="View"><i class="fas fa-eye"></i></a>';
                        @endcan

                        @can('members.edit')
                        actions += '<a href="/members/' + id + '/edit" class="btn btn-sm btn-primary me-1" title="Edit"><i class="fas fa-edit"></i></a>';
                        @endcan

                        @can('members.delete')
                        actions += '<button type="button" class="btn btn-sm btn-danger" onclick="deleteMember(' + id + ')" title="Delete"><i class="fas fa-trash"></i></button>';
                        @endcan

                        return actions;
                    }
                }
            ],
            order: [[3, 'asc'], [2, 'asc']],
            lengthMenu: [[25, 50, 100, 250, 500, 1000], [25, 50, 100, 250, 500, 1000]],
            pageLength: 50,
            dom: 'Blfrtip',
            buttons: [
                { extend: 'copy', className: 'btn btn-secondary btn-sm' },
                { extend: 'csv', className: 'btn btn-secondary btn-sm' },
                { extend: 'excel', className: 'btn btn-secondary btn-sm' },
                { extend: 'pdf', className: 'btn btn-secondary btn-sm' },
                { extend: 'print', className: 'btn btn-secondary btn-sm' }
            ]
        });

        $('#btn-search').click(function() {
            dataTable.draw();
        });

        $('#btn-reset').click(function() {
            $('#search-column').val('all');
            $('#search-value').val('');
            dataTable.draw();
        });

        $('#search-value').keypress(function(e) {
            if(e.which == 13) {
                dataTable.draw();
            }
        });
    });

    function deleteMember(id) {
        deleteItem('{{ route("admin.members.index") }}/' + id, id);
    }
</script>
@endsection
