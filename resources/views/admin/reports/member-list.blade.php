@extends('admin.admin_dashboard')

@section('title', 'Member List Report')
@section('page-title', 'Member List Report')

@php
function formatPhone($phone) {
    if (!$phone) return '';
    $digits = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($digits) === 10) {
        return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6);
    }
    return $phone;
}

function formatDate($date) {
    if (!$date) return '';
    return \Carbon\Carbon::parse($date)->format('m/d/Y');
}
@endphp

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label">Search By</label>
                <select name="search_column" class="form-select">
                    <option value="all" {{ request('search_column') == 'all' ? 'selected' : '' }}>All Fields</option>
                    <option value="member_no" {{ request('search_column') == 'member_no' ? 'selected' : '' }}>Member No</option>
                    <option value="first_name" {{ request('search_column') == 'first_name' ? 'selected' : '' }}>First Name</option>
                    <option value="last_name" {{ request('search_column') == 'last_name' ? 'selected' : '' }}>Last Name</option>
                    <option value="phone" {{ request('search_column') == 'phone' ? 'selected' : '' }}>Phone</option>
                    <option value="city" {{ request('search_column') == 'city' ? 'selected' : '' }}>City</option>
                    <option value="joining_year" {{ request('search_column') == 'joining_year' ? 'selected' : '' }}>Joining Year</option>
                    <option value="status" {{ request('search_column') == 'status' ? 'selected' : '' }}>Status</option>
                    <option value="receipt_no" {{ request('search_column') == 'receipt_no' ? 'selected' : '' }}>Receipt No</option>
                    <option value="email" {{ request('search_column') == 'email' ? 'selected' : '' }}>Email</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search Value</label>
                <input type="text" name="search_value" class="form-control" placeholder="Search..." value="{{ request('search_value') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="ACTIVE" {{ request('status') == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                    <option value="INACTIVE" {{ request('status') == 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Membership Type</label>
                <select name="membership_type_id" class="form-select">
                    <option value="">All</option>
                    @foreach($membershipTypes as $type)
                    <option value="{{ $type->id }}" {{ request('membership_type_id') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Joining Year</label>
                <select name="year" class="form-select">
                    <option value="">All</option>
                    @foreach($years as $yr)
                    <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i></button>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <a href="{{ route('admin.reports.members') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-end mb-3">
            <div class="btn-group">
                <a href="{{ route('admin.reports.export-members', array_merge(request()->query(), ['format' => 'csv'])) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a href="{{ route('admin.reports.export-members', array_merge(request()->query(), ['format' => 'xlsx'])) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('admin.reports.export-members', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Member No</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Spouse First</th>
                        <th>Spouse Last</th>
                        <th>Date of Birth</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Cell Phone</th>
                        <th>Address 1</th>
                        <th>Address 2</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Zip</th>
                        <th>County</th>
                        <th>Joining Year</th>
                        <th>Status</th>
                        <th>Status Reason</th>
                        <th>Receipt No</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                    <tr>
                        <td>{{ $member->member_no }}</td>
                        <td>{{ $member->first_name }}</td>
                        <td>{{ $member->last_name }}</td>
                        <td>{{ $member->spouse_first_name }}</td>
                        <td>{{ $member->spouse_last_name }}</td>
                        <td>{{ formatDate($member->dateofbirth) }}</td>
                        <td>{{ $member->email }}</td>
                        <td>{{ formatPhone($member->phone) }}</td>
                        <td>{{ formatPhone($member->cell_phone) }}</td>
                        <td>{{ $member->address1 }}</td>
                        <td>{{ $member->address2 }}</td>
                        <td>{{ $member->city }}</td>
                        <td>{{ $member->state }}</td>
                        <td>{{ $member->zip }}</td>
                        <td>{{ $member->county }}</td>
                        <td>{{ $member->joining_year }}</td>
                        <td>
                            @if($member->status === 'ACTIVE')
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>{{ $member->status_reason }}</td>
                        <td>{{ $member->receipt_no }}</td>
                        <td>{{ $member->amount }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="20" class="text-center text-muted">No members found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            <strong>Total Members:</strong> {{ $members->count() }}
        </div>
    </div>
</div>
@endsection
