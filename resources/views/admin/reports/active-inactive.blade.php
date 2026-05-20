@extends('admin.admin_dashboard')

@section('title', $type === 'active' ? 'Active Members' : 'Inactive Members')
@section('page-title', $type === 'active' ? 'Active Members' : 'Inactive Members')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="btn-group">
            <a href="{{ route('admin.reports.active-inactive', ['type' => 'active']) }}" class="btn {{ $type === 'active' ? 'btn-primary' : 'btn-outline-primary' }}">
                Active ({{ \App\Models\Member::active()->count() }})
            </a>
            <a href="{{ route('admin.reports.active-inactive', ['type' => 'inactive']) }}" class="btn {{ $type === 'inactive' ? 'btn-primary' : 'btn-outline-primary' }}">
                Inactive ({{ \App\Models\Member::inactive()->count() }})
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Member No</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Membership Type</th>
                        <th>Joining Year</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $member)
                    <tr>
                        <td>{{ $member->member_no }}</td>
                        <td>{{ $member->full_name }}</td>
                        <td>{{ $member->email }}</td>
                        <td>{{ $member->phone }}</td>
                        <td>{{ $member->membershipType?->name }}</td>
                        <td>{{ $member->joining_year }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No members found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
