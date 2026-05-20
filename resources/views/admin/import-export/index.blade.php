@extends('admin.admin_dashboard')

@section('title', 'Import/Export')
@section('page-title', 'Import/Export')

@section('content')
<div class="row g-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-import me-2"></i>Import Members
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Import members from CSV or Excel file. The file should contain the following columns:
                    member_no, first_name, last_name, spouse_first_name, spouse_last_name, email, phone, 
                    address1, address2, city, state, zip, membership_type_name, membership_start_date, status, receipt_no
                </p>
                
                <div class="mb-3">
                    <a href="{{ route('admin.import-export.template') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download me-1"></i> Download Template
                    </a>
                </div>
                
                <form action="{{ route('admin.import-export.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select File</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Import
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-export me-2"></i>Export Members
            </div>
            <div class="card-body">
                <form action="{{ route('admin.import-export.export') }}" method="GET">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All</option>
                            <option value="ACTIVE">Active</option>
                            <option value="INACTIVE">Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Membership Type</label>
                        <select name="membership_type_id" class="form-select">
                            <option value="">All</option>
                            @foreach(\App\Models\MembershipType::active()->get() as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Format</label>
                        <div class="btn-group w-100">
                            <button type="submit" name="format" value="csv" class="btn btn-outline-primary">
                                <i class="fas fa-file-csv me-1"></i> CSV
                            </button>
                            <button type="submit" name="format" value="xlsx" class="btn btn-outline-primary">
                                <i class="fas fa-file-excel me-1"></i> Excel
                            </button>
                            <button type="submit" name="format" value="pdf" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-file-pdf me-1"></i> PDF
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
