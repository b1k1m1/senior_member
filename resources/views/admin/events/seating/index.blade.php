@extends('admin.admin_dashboard')
@section('title', 'Event Seating')
@section('page-title', 'Event Seating Plan')

@section('content')

    <div class="page-content">
        <div class="container-fluid">
            {{-- Page Header --}}
            <div class="row mb-3">
                <div class="col-md-8">


                    <div class="text-muted">
                        {{ $event->title }}
                    </div>
                </div>


                <div class="col-md-4 text-md-end">

                    @if ($totalTables > 0)
                        <a href="{{ route('events.seating.hall', $event->id) }}" class="btn btn-primary">

                            <i class="mdi mdi-table-chair me-1"></i>

                            Hall Layout

                        </a>
                    @endif

                    <a href="{{ url()->previous() }}" class="btn btn-secondary">
                        Back
                    </a>
                </div>
            </div>


            {{-- Success Message --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>
                </div>
            @endif


            {{-- Error Message --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}

                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                    </button>
                </div>
            @endif


            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            {{-- Event Information --}}
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Event Information</strong>
                </div>

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-4 mb-2">
                            <strong>Event:</strong>
                            <br>
                            {{ $event->title }}
                        </div>

                        <div class="col-md-3 mb-2">
                            <strong>Date:</strong>
                            <br>
                            {{ \Carbon\Carbon::parse($event->start_date)->format('m/d/Y') }}
                        </div>

                        <div class="col-md-3 mb-2">
                            <strong>Location:</strong>
                            <br>
                            {{ $event->location ?: '-' }}
                        </div>

                        <div class="col-md-2 mb-2">
                            <strong>Status:</strong>
                            <br>
                            {{ $event->status }}
                        </div>

                    </div>

                </div>
            </div>


            {{-- Seating Summary --}}
            <div class="row mb-4">

                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="text-muted mb-1">
                                Tables
                            </div>

                            <h3 class="mb-0">
                                {{ $totalTables }}
                            </h3>
                        </div>
                    </div>
                </div>


                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="text-muted mb-1">
                                Total Seats
                            </div>

                            <h3 class="mb-0">
                                {{ $totalSeats }}
                            </h3>
                        </div>
                    </div>
                </div>


                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="text-muted mb-1">
                                Expected People
                            </div>

                            <h3 class="mb-0">
                                {{ $expectedPeople }}
                            </h3>
                        </div>
                    </div>
                </div>


                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="text-muted mb-1">
                                Assigned
                            </div>

                            <h3 class="mb-0">
                                {{ $assignedSeats }}
                            </h3>
                        </div>
                    </div>
                </div>


                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="text-muted mb-1">
                                Available
                            </div>

                            <h3 class="mb-0">
                                {{ $availableSeats }}
                            </h3>
                        </div>
                    </div>
                </div>


                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="text-muted mb-1">
                                Unassigned
                            </div>

                            <h3 class="mb-0">
                                {{ $unassignedAttendees->count() }}
                            </h3>
                        </div>
                    </div>
                </div>

            </div>


            {{-- Generate Tables --}}
            @if ($totalTables === 0)
                <div class="card mb-4">

                    <div class="card-header">
                        <strong>Generate Seating Tables</strong>
                    </div>

                    <div class="card-body">

                        <form method="POST" action="{{ route('events.seating.generate', $event->id) }}">

                            @csrf

                            <div class="row align-items-end">

                                {{-- Number of Tables --}}
                                <div class="col-md-3 mb-3">

                                    <label for="number_of_tables" class="form-label">

                                        Number of Tables

                                    </label>

                                    <input type="number" name="number_of_tables" id="number_of_tables" class="form-control"
                                        min="1" max="200" value="{{ old('number_of_tables', 60) }}" required>

                                </div>


                                {{-- Seats Per Table --}}
                                <div class="col-md-3 mb-3">

                                    <label for="seats_per_table" class="form-label">

                                        Seats Per Table

                                    </label>

                                    <input type="number" name="seats_per_table" id="seats_per_table" class="form-control"
                                        min="1" max="50" value="{{ old('seats_per_table', 10) }}" required>

                                </div>


                                {{-- Table Shape --}}
                                <div class="col-md-3 mb-3">

                                    <label for="table_shape" class="form-label">

                                        Table Shape

                                    </label>

                                    <select name="table_shape" id="table_shape" class="form-select" required>

                                        <option value="ROUND"
                                            {{ old('table_shape', 'ROUND') === 'ROUND' ? 'selected' : '' }}>

                                            Round

                                        </option>

                                        <option value="RECTANGLE"
                                            {{ old('table_shape') === 'RECTANGLE' ? 'selected' : '' }}>

                                            Rectangle

                                        </option>

                                    </select>

                                </div>


                                {{-- Generate Button --}}
                                <div class="col-md-3 mb-3">

                                    <button type="submit" class="btn btn-primary w-100">

                                        Generate Tables

                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>
            @else
                {{-- Seating already generated --}}
                <div class="alert alert-info">

                    Seating tables have already been generated for this event.

                </div>
            @endif


            {{-- Generated Tables --}}
            @if ($totalTables > 0)

                <div class="card mb-4">

                    <div class="card-header d-flex justify-content-between align-items-center">

                        <strong>
                            Seating Tables
                        </strong>

                        <span class="text-muted">
                            {{ $assignedSeats }} of {{ $totalSeats }} seats assigned
                        </span>

                    </div>

                    <div class="card-body">

                        <div class="row">

                            @foreach ($tables as $table)
                                @php

                                    $tableSeats = $seats->where('event_table_id', $table->id);

                                    $tableAssigned = $tableSeats
                                        ->filter(function ($seat) {
                                            return !is_null($seat->member_id) || !is_null($seat->event_guest_id);
                                        })
                                        ->count();

                                    $tableAvailable = $tableSeats
                                        ->filter(function ($seat) {
                                            return $seat->seat_status === 'AVAILABLE' &&
                                                is_null($seat->member_id) &&
                                                is_null($seat->event_guest_id);
                                        })
                                        ->count();

                                    $tableBlocked = $tableSeats->where('seat_status', 'BLOCKED')->count();

                                @endphp


                                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">

                                    <div class="card h-100 border">

                                        <div class="card-body">

                                            <div class="d-flex justify-content-between align-items-start mb-3">

                                                <div>

                                                    <h5 class="mb-1">
                                                        {{ $table->table_name ?: 'Table ' . $table->table_no }}
                                                    </h5>

                                                    <small class="text-muted">
                                                        {{ ucfirst(strtolower($table->table_shape)) }}
                                                    </small>

                                                </div>


                                                @if ($tableAssigned >= $table->capacity)
                                                    <span class="badge bg-primary">
                                                        Full
                                                    </span>
                                                @elseif ($tableAssigned > 0)
                                                    <span class="badge bg-warning text-dark">
                                                        Partial
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        Empty
                                                    </span>
                                                @endif

                                            </div>


                                            <div class="mb-2">

                                                <div class="d-flex justify-content-between">

                                                    <span>
                                                        Capacity
                                                    </span>

                                                    <strong>
                                                        {{ $table->capacity }}
                                                    </strong>

                                                </div>

                                            </div>


                                            <div class="mb-2">

                                                <div class="d-flex justify-content-between">

                                                    <span>
                                                        Assigned
                                                    </span>

                                                    <strong>
                                                        {{ $tableAssigned }}
                                                    </strong>

                                                </div>

                                            </div>


                                            <div class="mb-2">

                                                <div class="d-flex justify-content-between">

                                                    <span>
                                                        Available
                                                    </span>

                                                    <strong>
                                                        {{ $tableAvailable }}
                                                    </strong>

                                                </div>

                                            </div>


                                            @if ($tableBlocked > 0)
                                                <div class="mb-2">

                                                    <div class="d-flex justify-content-between">

                                                        <span>
                                                            Blocked
                                                        </span>

                                                        <strong>
                                                            {{ $tableBlocked }}
                                                        </strong>

                                                    </div>

                                                </div>
                                            @endif


                                            {{-- Progress --}}
                                            @php

                                                $percentage =
                                                    $table->capacity > 0
                                                        ? round(($tableAssigned / $table->capacity) * 100)
                                                        : 0;

                                                if ($percentage > 100) {
                                                    $percentage = 100;
                                                }

                                            @endphp


                                            <div class="progress mt-3 mb-3" style="height: 8px;">

                                                <div class="progress-bar" role="progressbar"
                                                    style="width: {{ $percentage }}%;"
                                                    aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                </div>

                                            </div>


                                            <a href="{{ route('events.seating.table', [
                                                'eventId' => $event->id,
                                                'tableId' => $table->id,
                                            ]) }}"
                                                class="btn btn-outline-primary w-100">

                                                View Table

                                            </a>

                                        </div>

                                    </div>

                                </div>
                            @endforeach

                        </div>

                    </div>

                </div>

            @endif


            {{-- Unassigned Members --}}
            @if ($unassignedAttendees->count() > 0)

                <div class="card">

                    <div class="card-header">

                        <strong>
                            Unassigned Confirmed Members
                        </strong>

                        <span class="badge bg-danger ms-2">
                            {{ $unassignedAttendees->count() }}
                        </span>

                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-hover align-middle">

                                <thead>

                                    <tr>

                                        <th style="width: 60px;">
                                            #
                                        </th>

                                        <th style="width: 140px;">
                                            Member No.
                                        </th>

                                        <th>
                                            Member Name
                                        </th>

                                        <th style="width: 120px;">
                                            Guests
                                        </th>

                                        <th style="width: 140px;">
                                            Party Size
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach ($unassignedAttendees as $index => $attendee)
                                        @php

                                            $member = $members->get($attendee->member_id);

                                            $guestCount = (int) ($attendee->guests_count ?? 0);

                                            $partySize = 1 + $guestCount;

                                        @endphp


                                        <tr>

                                            <td>
                                                {{ $index + 1 }}
                                            </td>


                                            <td>
                                                {{ $member->member_no ?? '-' }}
                                            </td>


                                            <td>

                                                @if ($member)
                                                    {{ $member->last_name }},
                                                    {{ $member->first_name }}
                                                @else
                                                    <span class="text-danger">
                                                        Member not found
                                                    </span>
                                                @endif

                                            </td>


                                            <td>
                                                {{ $guestCount }}
                                            </td>


                                            <td>
                                                {{ $partySize }}
                                            </td>

                                        </tr>
                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            @endif

        </div>
    </div>

@endsection
