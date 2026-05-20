@extends('admin.admin_dashboard')

@section('title', 'Preview Member Import')
@section('page-title', 'Preview Member Import')


@section('content')

<div class="page-content">
    <div class="container-fluid">

        {{-- Page Title --}}
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Preview Member Import</h4>

                    <div class="page-title-right">
                        <a href="{{ route('admin.members.import.form') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Card --}}
        <div class="row">
            <div class="col-md-4">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Total valid rows found:</strong> {{ $totalRows }} <br>
                            Showing first {{ min($previewLimit, $totalRows) }} rows only for preview.
                            Nothing has been saved yet. Click <strong>Confirm Import</strong> to import all rows.
                        </div>
                        <h6 class="text-muted mb-1">Total Rows Ready To Import</h6>
                        <h3 class="mb-0">{{ number_format($totalRows) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview Table --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-table me-1"></i> Excel Data Preview
                </h5>

                <form method="POST"
                      action="{{ route('admin.members.import.confirm') }}"
                      onsubmit="return confirm('Are you sure you want to import these members into the members table?');">
                    @csrf

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Confirm Import
                    </button>
                </form>
            </div>

            <div class="card-body">

                @if($totalRows == 0)
                    <div class="alert alert-warning mb-0">
                        No valid rows found in the uploaded Excel file.
                    </div>
                @else

                    <div class="alert alert-info">
                        Please review the data below. Nothing has been saved yet.
                        Click <strong>Confirm Import</strong> only after checking the preview.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Row</th>
                                    <th>Listing No</th>
                                    <th>Receipt No</th>
                                    <th>Last Name</th>
                                    <th>First Name</th>
                                    <th>Address</th>
                                    <th>City</th>
                                    <th>State</th>
                                    <th>Zip</th>
                                    <th>County</th>
                                    <th>DOB</th>
                                    <th>Phone Home</th>
                                    <th>Phone Cell</th>
                                    <th>Fee</th>
                                    <th>Email</th>
                                    <th>Joining Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($previewRows as $row)
                                    <tr>
                                        <td>{{ $row['row_number'] }}</td>
                                        <td>{{ $row['listing_no'] }}</td>
                                        <td>{{ $row['receipt_no'] }}</td>
                                        <td>{{ $row['last_name'] }}</td>
                                        <td>{{ $row['first_name'] }}</td>
                                        <td>{{ $row['address'] }}</td>
                                        <td>{{ $row['city'] }}</td>
                                        <td>{{ $row['state'] }}</td>
                                        <td>{{ $row['zip_code'] }}</td>
                                        <td>{{ $row['county'] }}</td>
                                        <td>{{ $row['date_of_birth'] }}</td>
                                        <td>{{ $row['phone_home'] }}</td>
                                        <td>{{ $row['phone_cell'] }}</td>
                                        <td>{{ $row['fee'] }}</td>
                                        <td>{{ $row['email_addresses'] }}</td>
                                        <td>{{ $row['joining_year'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @endif

            </div>
        </div>

    </div>
</div>

@endsection
