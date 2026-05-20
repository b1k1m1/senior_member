@extends('admin.admin_dashboard')

@section('title', 'Dues Summary Report')
@section('page-title', 'Dues Summary Report')

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    @for($y = date('Y'); $y >= date('Y') - 10; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5>Total Expected</h5>
                <h3>${{ number_format($totalExpected, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>Total Collected</h5>
                <h3>${{ number_format($totalCollected, 2) }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning">
            <div class="card-body">
                <h5>Outstanding</h5>
                <h3>${{ number_format($totalExpected - $totalCollected, 2) }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-chart-bar me-1"></i> Collection by Membership Type - {{ $year }}
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Membership Type</th>
                        <th>Members</th>
                        <th>Expected</th>
                        <th>Collected</th>
                        <th>Outstanding</th>
                        <th>Collection Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($membershipTypes as $type)
                    <tr>
                        <td>{{ $type->name }}</td>
                        <td>{{ $type->members_count }}</td>
                        <td>${{ number_format($type->members_count * $type->fee_amount, 2) }}</td>
                        <td>
                            @php
                                $collected = $paymentsByType->where('name', $type->name)->first();
                            @endphp
                            ${{ number_format($collected?->total_collected ?? 0, 2) }}
                        </td>
                        <td>
                            @php
                                $expected = $type->members_count * $type->fee_amount;
                                $outstanding = $expected - ($collected?->total_collected ?? 0);
                            @endphp
                            ${{ number_format($outstanding, 2) }}
                        </td>
                        <td>
                            @php
                                $rate = $expected > 0 ? round(($collected?->total_collected ?? 0) / $expected * 100, 1) : 0;
                            @endphp
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-{{ $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }}" role="progressbar" style="width: {{ $rate }}%;">
                                    {{ $rate }}%
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No data available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
