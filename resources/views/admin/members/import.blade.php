@extends('admin.admin_dashboard')
@section('title', 'Import from Excel')
@section('page-title', 'Import Member')

@section('content')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Import Members From Excel</h4>

                    <div class="page-title-right">
                        <a href="{{ route('admin.members.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-excel me-1"></i> Upload Member Excel File
                        </h5>
                    </div>

                    <div class="card-body">

                        {{-- Success Message --}}
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-1"></i>
                                {{ session('success') }}

                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Error Message --}}
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{ session('error') }}

                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Validation Errors --}}
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <strong>Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Import Row Warnings --}}
                        @if(session('import_errors') && count(session('import_errors')) > 0)
                            <div class="alert alert-warning">
                                <strong>Import Warnings:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach(session('import_errors') as $importError)
                                        <li>{{ $importError }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- Upload Form --}}
                        <form method="POST"
                              action="{{ route('admin.members.import.preview') }}"
                              enctype="multipart/form-data">

                            @csrf

                            <div class="row align-items-end">

                                <div class="col-md-7 mb-3">
                                    <label for="excel_file" class="form-label">
                                        Select Excel File
                                    </label>

                                    <input type="file"
                                           name="excel_file"
                                           id="excel_file"
                                           class="form-control"
                                           accept=".xlsx,.xls,.csv"
                                           required>

                                    <small class="text-muted">
                                        Allowed file types: .xlsx, .xls, .csv
                                    </small>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-eye"></i> Preview Import
                                    </button>
                                </div>

                            </div>

                        </form>

                        {{-- Instructions --}}
                        <div class="alert alert-info mt-4 mb-0">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-1"></i> Expected Excel Header
                            </h6>

                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered mb-0 bg-white">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Excel Column</th>
                                            <th>Members Table Field</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>ID</td>
                                            <td>Ignored</td>
                                        </tr>
                                        <tr>
                                            <td>Listing No</td>
                                            <td>member_no</td>
                                        </tr>
                                        <tr>
                                            <td>Receipt No</td>
                                            <td>receipt_no</td>
                                        </tr>
                                        <tr>
                                            <td>Last Name</td>
                                            <td>last_name</td>
                                        </tr>
                                        <tr>
                                            <td>First Name</td>
                                            <td>first_name</td>
                                        </tr>
                                        <tr>
                                            <td>Address</td>
                                            <td>address1</td>
                                        </tr>
                                        <tr>
                                            <td>City</td>
                                            <td>city</td>
                                        </tr>
                                        <tr>
                                            <td>State</td>
                                            <td>state</td>
                                        </tr>
                                        <tr>
                                            <td>Zip Code</td>
                                            <td>zip</td>
                                        </tr>
                                        <tr>
                                            <td>County</td>
                                            <td>county</td>
                                        </tr>
                                        <tr>
                                            <td>Date of Birth</td>
                                            <td>dateofbirth</td>
                                        </tr>
                                        <tr>
                                            <td>Phone(Home)</td>
                                            <td>phone</td>
                                        </tr>
                                        <tr>
                                            <td>Phone(cell)</td>
                                            <td>cell_phone</td>
                                        </tr>
                                        <tr>
                                            <td>Fee</td>
                                            <td>amount</td>
                                        </tr>
                                        <tr>
                                            <td>Email Addresses</td>
                                            <td>email</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <hr>

                            <p class="mb-1">
                                <strong>Default Membership Type:</strong> LIFE
                            </p>

                            <p class="mb-0">
                                If the same <strong>Listing No</strong> already exists, the member will be updated.
                                If it does not exist, a new member will be created.
                            </p>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
