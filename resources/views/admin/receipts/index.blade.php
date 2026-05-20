@extends('admin.admin_dashboard')

@section('title', 'Receipts')
@section('page-title', 'Receipts')

@section('page-actions')
<a href="{{ route('admin.receipts.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> New Receipt
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <table id="receipts-table" class="table table-striped dt-responsive nowrap" style="width: 100%;">
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Type</th>
                    <th>Received From</th>
                    <th>Amount</th>
                    <th>Payment Mode</th>
                    <th>Date</th>
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
        $('#receipts-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin.receipts.ajax") }}',
                type: 'GET'
            },
            columns: [
                { data: 'receipt_no', name: 'receipt_no' },
                { data: 'receipt_type', name: 'receiptType.name' },
                { data: 'received_from', name: 'received_from' },
                { data: 'amount', name: 'amount', render: function(data) { return '$' + parseFloat(data).toFixed(2); } },
                { data: 'payment_mode', name: 'payment_mode' },
                { data: 'created_at', name: 'created_at' },
                { 
                    data: 'id', 
                    name: 'actions', 
                    orderable: false, 
                    searchable: false,
                    render: function(id) {
                        return '<a href="/receipts/' + id + '" class="btn btn-sm btn-info me-1" title="View"><i class="fas fa-eye"></i></a>' +
                               '<a href="/receipts/' + id + '/print" class="btn btn-sm btn-primary me-1" target="_blank" title="Print"><i class="fas fa-print"></i></a>' +
                               '<a href="/receipts/' + id + '/edit" class="btn btn-sm btn-warning me-1" title="Edit"><i class="fas fa-edit"></i></a>' +
                               '<button type="button" class="btn btn-sm btn-danger" onclick="deleteItem(' + id + ')" title="Delete"><i class="fas fa-trash"></i></button>';
                    }
                }
            ],
            lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
            pageLength: 10
        });
    });
    
    function deleteItem(id) {
        deleteItem('{{ route("admin.receipts.destroy", ":id") }}'.replace(':id', id), id);
    }
</script>
@endsection
