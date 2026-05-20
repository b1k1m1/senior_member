@extends('admin.admin_dashboard')

@section('title', 'Event Dashboard')
@section('page-title', 'Event Dashboard')

@section('page-actions')
<a href="{{ route('admin.events.create') }}" class="btn btn-primary">
    <i class="fas fa-plus me-1"></i> New Event
</a>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small">Total Events</div>
                        <div class="fs-4 fw-bold">{{ $totalEvents }}</div>
                    </div>
                    <div><i class="fas fa-calendar-alt fs-1 opacity-50"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small">Active Events</div>
                        <div class="fs-4 fw-bold">{{ $activeEvents }}</div>
                    </div>
                    <div><i class="fas fa-check-circle fs-1 opacity-50"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small">Total Attendees</div>
                        <div class="fs-4 fw-bold">{{ $totalAttendees }}</div>
                    </div>
                    <div><i class="fas fa-users fs-1 opacity-50"></i></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small">Total Revenue</div>
                        <div class="fs-4 fw-bold">${{ number_format($totalRevenue, 2) }}</div>
                    </div>
                    <div><i class="fas fa-dollar-sign fs-1 opacity-50"></i></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">This Month ({{ now()->format('F Y') }})</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="fs-3 fw-bold text-success">{{ $thisMonthConfirmed }}</div>
                            <div class="text-muted">Confirmed Attendees</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="fs-3 fw-bold text-warning">{{ $thisMonthTentative }}</div>
                            <div class="text-muted">Tentative Attendees</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <div class="fs-3 fw-bold">{{ $currentMonthEvents->count() }}</div>
                            <div class="text-muted">Events This Month</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="fas fa-clock me-1"></i> Upcoming Events</h5>
            </div>
            <div class="card-body p-0">
                @if($futureEvents->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($futureEvents as $event)
                    <a href="{{ route('admin.events.show', $event->id) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $event->title }}</div>
                                <small class="text-muted">{{ $event->start_date->format('M j, Y') }}</small>
                            </div>
                            <span class="badge bg-primary">{{ $event->confirmed_count }} confirmed</span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="p-3 text-center text-muted">No upcoming events</div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-calendar me-1"></i> This Month Events</h5>
            </div>
            <div class="card-body p-0">
                @if($currentMonthEvents->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($currentMonthEvents as $event)
                    <a href="{{ route('admin.events.show', $event->id) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $event->title }}</div>
                                <small class="text-muted">{{ $event->start_date->format('M j') }} - {{ $event->end_date->format('M j, Y') }}</small>
                            </div>
                            <span class="badge {{ $event->status == 'ACTIVE' ? 'bg-success' : 'bg-secondary' }}">{{ $event->status }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="p-3 text-center text-muted">No events this month</div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="fas fa-history me-1"></i> Past Events</h5>
            </div>
            <div class="card-body p-0">
                @if($pastEvents->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($pastEvents as $event)
                    <a href="{{ route('admin.events.show', $event->id) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $event->title }}</div>
                                <small class="text-muted">{{ $event->start_date->format('M j, Y') }}</small>
                            </div>
                            <span class="badge bg-info">{{ $event->confirmed_count }} / {{ $event->tentative_count }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="p-3 text-center text-muted">No past events</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
