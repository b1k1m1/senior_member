@extends('admin.admin_dashboard')

@section('title', 'Payments')
@section('page-title', 'Payments')

@section('page-actions')
<a href="{{ route('admin.payments.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> Add Payment
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <table id="payments-table" class="table table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Member</th>
                    <th>Amount</th>
                    <th>Method</th>
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
    $(function() {
        $('#payments-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.payments.ajax") }}',
                type: 'GET'
            },
            columns: [
                { data: 'payment_date', name: 'payment_date' },
                { data: 'member_name', name: 'member.last_name' },
                { 
                    data: 'amount', 
                    name: 'amount',
                    render: function(data) {
                        return '$' + parseFloat(data).toFixed(2);
                    }
                },
                { 
                    data: 'method', 
                    name: 'method',
                    render: function(data) {
                        return '<span class="badge bg-info">' + data + '</span>';
                    }
                },
                { data: 'receipt_no', name: 'receipt_no' },
                { 
                    data: 'id', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function(id) {
                        return '<button type="button" class="btn btn-sm btn-danger" onclick="deletePayment(' + id + ')"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
            order: [[0, 'desc']],
            lengthMenu: [[10, 25, 50, 100, 250, 500, 1000], [10, 25, 50, 100, 250, 500, 1000]],
            pageLength: 50
        });
    });
    
    function deletePayment(id) {
        deleteItem('{{ route("admin.payments.index") }}/' + id, id);
    }
</script>
@endsection
