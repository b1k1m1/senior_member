@extends('admin.admin_dashboard')

@section('title', 'Event Details')
@section('page-title', 'Event Details')

@section('page-actions')
<a href="{{ route('admin.events.index') }}" class="btn btn-secondary me-2">
    <i class="fas fa-arrow-left me-1"></i> Back
</a>
<a href="{{ route('admin.events.edit', $event->id) }}" class="btn btn-primary">
    <i class="fas fa-edit me-1"></i> Edit
</a>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Event Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Title</label>
                        <p class="fw-bold">{{ $event->title }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Event Type</label>
                        <p>{{ $event->eventType?->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Start Date</label>
                        <p>{{ $event->start_date->format('F j, Y') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">End Date</label>
                        <p>{{ $event->end_date->format('F j, Y') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Time</label>
                        <p>
                            {{ $event->start_time ? $event->start_time->format('h:i A') : 'N/A' }}
                            {{ $event->end_time ? ' - ' . $event->end_time->format('h:i A') : '' }}
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Location</label>
                        <p>{{ $event->location ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Price</label>
                        <p>${{ number_format($event->price, 2) }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Status</label>
                        <p>
                            @php
                                $badges = [
                                    'ACTIVE' => 'bg-success',
                                    'SCHEDULED' => 'bg-primary',
                                    'RESCHEDULED' => 'bg-warning',
                                    'CANCELLED' => 'bg-danger',
                                    'COMPLETED' => 'bg-secondary'
                                ];
                            @endphp
                            <span class="badge {{ $badges[$event->status] ?? 'bg-secondary' }}">{{ $event->status }}</span>
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Attendance Type</label>
                        <p>
                            @if($event->attendance_type == 'MEMBERS_ONLY')
                                Members Only
                            @else
                                Members with Guests
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Confirmation Deadline</label>
                        <p>{{ $event->confirmation_deadline?->format('F j, Y') ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Capacity</label>
                        <p>{{ $event->capacity ?? 'Unlimited' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Min Attendees</label>
                        <p>{{ $event->min_attendees ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted">Max Guests per Member</label>
                        <p>{{ $event->max_guests_per_member ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="text-muted">Description</label>
                        <p>{{ $event->description ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="text-muted">Notes</label>
                        <p>{{ $event->notes ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Attendance Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span>Confirmed:</span>
                    <span class="badge bg-success">{{ $event->confirmed_count }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Tentative:</span>
                    <span class="badge bg-warning">{{ $event->tentative_count }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Guests:</span>
                    <span>{{ $event->total_guests }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Available Slots:</span>
                    <span>{{ $event->available_slots == PHP_INT_MAX ? 'Unlimited' : $event->available_slots }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between">
                    <span>Total Revenue:</span>
                    <span class="fw-bold">${{ number_format($event->total_revenue, 2) }}</span>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Attendees</h5>
                <a href="{{ route('admin.event-attendees.create', ['event' => $event->id]) }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Add
                </a>
            </div>
            <div class="card-body p-0">
                @if($event->attendees->count() > 0)
                <div class="list-group list-group-flush">
                    @foreach($event->attendees as $attendee)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $attendee->member?->first_name }} {{ $attendee->member?->last_name }}</strong>
                                @if($attendee->guests_count > 0)
                                <br><small class="text-muted">{{ $attendee->guests_count }} guest(s)</small>
                                @endif
                            </div>
                            <span class="badge {{ $attendee->status == 'CONFIRMED' ? 'bg-success' : ($attendee->status == 'TENTATIVE' ? 'bg-warning' : 'bg-danger') }}">
                                {{ $attendee->status }}
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-3 text-center text-muted">No attendees yet</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
