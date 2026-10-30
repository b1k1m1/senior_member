@extends('admin.admin_dashboard')

@section('title', 'Table Seating')
@section('page-title', 'Table Seating')

@section('content')
    <div class="page-content">
        <div class="container-fluid">

            {{-- ============================================================= --}}
            {{-- PAGE HEADER --}}
            {{-- ============================================================= --}}
            <div class="row mb-3">
                <div class="col-md-8">
                    <h4 class="mb-1">
                        {{ $table->table_name ?: 'Table ' . $table->table_no }}
                    </h4>
                    <div class="text-muted">
                        {{ $event->title }}
                    </div>
                </div>

                <div class="col-md-4 text-md-end">
                    <a href="{{ route('events.seating.index', $event->id) }}" class="btn btn-secondary">
                        Back to Seating Plan
                    </a>
                </div>
            </div>

            {{-- ============================================================= --}}
            {{-- MESSAGES --}}
            {{-- ============================================================= --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ============================================================= --}}
            {{-- COUNTS --}}
            {{-- ============================================================= --}}
            @php
                $assignedCount = $seats
                    ->filter(function ($seat) {
                        return !is_null($seat->member_id) || !is_null($seat->event_guest_id);
                    })
                    ->count();

                $availableCount = $seats
                    ->filter(function ($seat) {
                        return $seat->seat_status === 'AVAILABLE' &&
                            is_null($seat->member_id) &&
                            is_null($seat->event_guest_id);
                    })
                    ->count();

                $blockedCount = $seats->where('seat_status', 'BLOCKED')->count();
            @endphp

            {{-- ============================================================= --}}
            {{-- SUMMARY --}}
            {{-- ============================================================= --}}
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="text-muted">Capacity</div>
                            <h3 class="mb-0">{{ $table->capacity }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="text-muted">Assigned</div>
                            <h3 class="mb-0 text-success">{{ $assignedCount }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="text-muted">Available</div>
                            <h3 class="mb-0 text-primary">{{ $availableCount }}</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <div class="text-muted">Blocked</div>
                            <h3 class="mb-0">{{ $blockedCount }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================= --}}
            {{-- GRAPHICAL TABLE --}}
            {{-- ============================================================= --}}
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Graphical Seating Layout</strong>
                    <span class="text-muted">
                        {{ ucfirst(strtolower($table->table_shape)) }} Table
                    </span>
                </div>

                <div class="card-body">
                    {{-- Legend --}}
                    <div class="d-flex flex-wrap justify-content-center gap-4 mb-4">

                        <div>
                            <span class="seat-legend seat-available"></span>
                            Available
                        </div>

                        <div>
                            <span class="seat-legend seat-assigned"></span>
                            Member
                        </div>

                        <div>
                            <span class="seat-legend seat-guest"></span>
                            Guest
                        </div>

                        <div>
                            <span class="seat-legend seat-blocked"></span>
                            Blocked
                        </div>

                    </div>
                    <div class="seating-layout-wrapper">
                        <div class="seating-layout">

                            {{-- Center Table --}}
                            <div
                                class="center-table {{ $table->table_shape === 'RECTANGLE' ? 'center-table-rectangle' : 'center-table-round' }}">
                                <div class="table-number">
                                    Table {{ $table->table_no }}
                                </div>
                                <div class="table-count">
                                    {{ $assignedCount }} / {{ $table->capacity }}
                                </div>
                            </div>

                            {{-- Graphical Seats --}}
                            @foreach ($seats as $index => $seat)
                                @php
                                    $member = null;
                                    $guest = null;

                                    if ($seat->member_id) {
                                        $member = $seatMembers->get($seat->member_id);
                                    }

                                    if ($seat->event_guest_id) {
                                        $guest = $guests->get($seat->event_guest_id);
                                    }

                                    $isAssigned = !is_null($seat->member_id) || !is_null($seat->event_guest_id);
                                    $isBlocked = $seat->seat_status === 'BLOCKED';

                                    $totalSeatCount = max($seats->count(), 1);
                                    $angle = ($index / $totalSeatCount) * 360 - 90;
                                    $radius = 185;
                                    $x = 250 + $radius * cos(deg2rad($angle));
                                    $y = 250 + $radius * sin(deg2rad($angle));

                                    if ($isBlocked) {
                                        $seatClass = 'seat-blocked';
                                    } elseif ($guest) {
                                        $seatClass = 'seat-guest';
                                    } elseif ($member) {
                                        $seatClass = 'seat-assigned';
                                    } else {
                                        $seatClass = 'seat-available';
                                    }

                                    $displayName = '';

                                    if ($member) {
                                        $displayName = $member->first_name . ' ' . $member->last_name;
                                    } elseif ($guest) {
                                        $displayName = $guest->guest_name ?: 'Guest';
                                    }

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Tooltip
                                    |--------------------------------------------------------------------------
                                    */

                                    $tooltip = 'Seat ' . $seat->seat_no;

                                    if ($member) {
                                        $tooltip .=
                                            ' - ' .
                                            $member->first_name .
                                            ' ' .
                                            $member->last_name .
                                            ' (' .
                                            $member->member_no .
                                            ')';
                                    } elseif ($guest) {
                                        $tooltip .= ' - ' . ($guest->guest_name ?: 'Guest');

                                        $guestParentAttendee = $attendeesById->get($guest->event_attendee_id);

                                        $guestSponsor = $guestParentAttendee
                                            ? $members->get($guestParentAttendee->member_id)
                                            : null;

                                        if ($guestSponsor) {
                                            $tooltip .=
                                                ' | Guest of: ' .
                                                $guestSponsor->first_name .
                                                ' ' .
                                                $guestSponsor->last_name .
                                                ' (' .
                                                $guestSponsor->member_no .
                                                ')';
                                        }
                                    } elseif ($isBlocked) {
                                        $tooltip .= ' - Blocked';
                                    }
                                @endphp
                                @if (!$isBlocked && !$isAssigned)
                                    <a href="javascript:void(0);"
                                        class="graphical-seat {{ $seatClass }} assign-seat-btn"
                                        data-seat-id="{{ $seat->id }}" data-seat-no="{{ $seat->seat_no }}"
                                        data-member-action="{{ route('events.seating.assign.member', [
                                            'eventId' => $event->id,
                                            'seatId' => $seat->id,
                                        ]) }}"
                                        data-guest-action="{{ route('events.seating.assign.guest', [
                                            'eventId' => $event->id,
                                            'seatId' => $seat->id,
                                        ]) }}"
                                        style="left: {{ $x }}px; top: {{ $y }}px;"
                                        title="Assign member or guest to Seat {{ $seat->seat_no }}">

                                        <div class="seat-number">
                                            {{ $seat->seat_no }}
                                        </div>

                                        <div class="seat-person">
                                            Available
                                        </div>

                                    </a>
                                @else
                                    <a href="#seat-card-{{ $seat->id }}" class="graphical-seat {{ $seatClass }}"
                                        style="left: {{ $x }}px; top: {{ $y }}px;"
                                        title="{{ $tooltip }}">

                                        <div class="seat-number">
                                            {{ $seat->seat_no }}
                                        </div>

                                        @if ($displayName)
                                            <div class="seat-person">
                                                {{ \Illuminate\Support\Str::limit($displayName, 16) }}
                                            </div>
                                        @elseif ($isBlocked)
                                            <div class="seat-person">
                                                Blocked
                                            </div>
                                        @else
                                            <div class="seat-person">
                                                Available
                                            </div>
                                        @endif

                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================= --}}
            {{-- SEAT ASSIGNMENT CARDS --}}
            {{-- ============================================================= --}}
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Seat Assignment</strong>
                </div>

                <div class="card-body">
                    <div class="row">
                        @foreach ($seats as $seat)
                            @php
                                $member = null;
                                $guest = null;

                                if ($seat->member_id) {
                                    $member = $seatMembers->get($seat->member_id);
                                }

                                if ($seat->event_guest_id) {
                                    $guest = $guests->get($seat->event_guest_id);
                                }

                                $isAssigned = !is_null($seat->member_id) || !is_null($seat->event_guest_id);
                                $isBlocked = $seat->seat_status === 'BLOCKED';
                            @endphp

                            <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                                <div class="card h-100 border seat-assignment-card" id="seat-card-{{ $seat->id }}">

                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <strong>Seat {{ $seat->seat_no }}</strong>

                                        @if ($isBlocked)
                                            <span class="badge bg-dark">Blocked</span>
                                        @elseif ($isAssigned)
                                            <span class="badge bg-success">Assigned</span>
                                        @else
                                            <span class="badge bg-secondary">Available</span>
                                        @endif
                                    </div>

                                    <div class="card-body">
                                        {{-- Assigned Member --}}
                                        @if ($member)
                                            <div class="assigned-person mb-3">
                                                <div class="text-muted small">Member</div>
                                                <div class="fw-bold fs-6">
                                                    {{ $member->first_name }} {{ $member->last_name }}
                                                </div>
                                                <div class="small text-muted">
                                                    Member No: {{ $member->member_no }}
                                                </div>
                                            </div>

                                            {{-- Assigned Guest --}}
                                        @elseif ($guest)
                                            <div class="assigned-person mb-3">
                                                <div class="text-muted small">Guest</div>
                                                @php
                                                    $guestParentAttendee = $guest
                                                        ? $attendeesById->get($guest->event_attendee_id)
                                                        : null;

                                                    $guestSponsor = $guestParentAttendee
                                                        ? $members->get($guestParentAttendee->member_id)
                                                        : null;
                                                @endphp


                                                <div class="fw-bold">
                                                    {{ $guest->guest_name ?: 'Guest' }}
                                                </div>


                                                <div class="small text-muted mb-1">
                                                    {{ ucfirst(strtolower($guest->guest_type)) }}
                                                </div>


                                                @if ($guestSponsor)
                                                    <div class="small">

                                                        <span class="text-muted">
                                                            Guest of:
                                                        </span>

                                                        <strong>
                                                            {{ $guestSponsor->first_name }}
                                                            {{ $guestSponsor->last_name }}
                                                        </strong>

                                                        <span class="text-muted">
                                                            ({{ $guestSponsor->member_no }})
                                                        </span>

                                                    </div>
                                                @endif
                                            </div>

                                            {{-- Empty --}}
                                        @else
                                            <div class="text-muted mb-3">
                                                Seat is currently available.
                                            </div>
                                        @endif

                                        {{-- Assignment Controls --}}
                                        @if (!$isBlocked && !$isAssigned)
                                            @if ($availableMembers->isNotEmpty())
                                                <form method="POST"
                                                    action="{{ route('events.seating.assign.member', [
                                                        'eventId' => $event->id,
                                                        'seatId' => $seat->id,
                                                    ]) }}">
                                                    @csrf

                                                    <div class="mb-3">
                                                        <label class="form-label" for="member_{{ $seat->id }}">
                                                            Assign Member
                                                        </label>

                                                        <select name="member_id" id="member_{{ $seat->id }}"
                                                            class="form-select member-select" required>
                                                            <option value="">Select Member</option>

                                                            @foreach ($availableMembers as $memberOption)
                                                                <option value="{{ $memberOption->id }}">
                                                                    {{ $memberOption->member_no }} -
                                                                    {{ $memberOption->last_name }},
                                                                    {{ $memberOption->first_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <button type="submit" class="btn btn-primary w-100">
                                                        Assign Member
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($availableMembers->isNotEmpty() && $availableGuests->isNotEmpty())
                                                <div class="text-center text-muted my-2">OR</div>
                                            @endif

                                            @if ($availableGuests->isNotEmpty())
                                                <form method="POST"
                                                    action="{{ route('events.seating.assign.guest', [
                                                        'eventId' => $event->id,
                                                        'seatId' => $seat->id,
                                                    ]) }}">
                                                    @csrf

                                                    <div class="mb-3">
                                                        <label class="form-label" for="guest_{{ $seat->id }}">
                                                            Assign Guest
                                                        </label>

                                                        <select name="event_guest_id" id="guest_{{ $seat->id }}"
                                                            class="form-select guest-select" required>
                                                            <option value="">Select Guest</option>

                                                            @foreach ($availableGuests as $guestOption)
                                                                @php
                                                                    $parentAttendee = $attendeesById->get(
                                                                        $guestOption->event_attendee_id,
                                                                    );

                                                                    $sponsorMember = $parentAttendee
                                                                        ? $members->get($parentAttendee->member_id)
                                                                        : null;
                                                                @endphp

                                                                <option value="{{ $guestOption->id }}">

                                                                    {{ $guestOption->guest_name }}

                                                                    @if ($sponsorMember)
                                                                        — Guest of
                                                                        {{ $sponsorMember->first_name }}
                                                                        {{ $sponsorMember->last_name }}
                                                                        ({{ $sponsorMember->member_no }})
                                                                    @endif

                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <button type="submit" class="btn btn-info w-100">
                                                        Assign Guest
                                                    </button>
                                                </form>
                                            @endif

                                            @if ($availableMembers->isEmpty() && $availableGuests->isEmpty())
                                                <div class="alert alert-light border mb-0">
                                                    No unassigned confirmed members or guests are available.
                                                </div>
                                            @endif
                                        @endif

                                        {{-- Clear Seat --}}
                                        @if (!$isBlocked && $isAssigned)
                                            <form method="POST"
                                                action="{{ route('events.seating.clear', [
                                                    'eventId' => $event->id,
                                                    'seatId' => $seat->id,
                                                ]) }}">
                                                @csrf

                                                <button type="submit" class="btn btn-outline-danger w-100"
                                                    onclick="return confirm('Remove this seat assignment?');">
                                                    Clear Seat
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ============================================================= --}}
            {{-- ASSIGN SEAT MODAL --}}
            {{-- ============================================================= --}}
            <div class="modal fade" id="assignSeatModal" tabindex="-1" aria-labelledby="assignSeatModalLabel"
                aria-hidden="true">

                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title" id="assignSeatModalLabel">
                                Assign Seat
                            </h5>

                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <form method="POST" id="assignSeatForm" action="">
                            @csrf

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="modalSeatLabel" class="form-label">
                                        Seat
                                    </label>

                                    <input type="text" id="modalSeatLabel" class="form-control" readonly>
                                </div>

                                @if ($availableMembers->isNotEmpty() || $availableGuests->isNotEmpty())
                                    <div class="mb-3">
                                        <label for="modal_assignment_type" class="form-label">
                                            Assign
                                        </label>

                                        <select id="modal_assignment_type" class="form-select">
                                            @if ($availableMembers->isNotEmpty())
                                                <option value="MEMBER">Member</option>
                                            @endif

                                            @if ($availableGuests->isNotEmpty())
                                                <option value="GUEST">Guest</option>
                                            @endif
                                        </select>
                                    </div>
                                @endif

                                <div class="mb-3" id="modal-member-section"
                                    @if ($availableMembers->isEmpty()) style="display:none;" @endif>

                                    <label for="modal_member_id" class="form-label">
                                        Member
                                    </label>

                                    <select name="member_id" id="modal_member_id" class="form-select">
                                        <option value="">Select Member</option>

                                        @foreach ($availableMembers as $memberOption)
                                            <option value="{{ $memberOption->id }}">
                                                {{ $memberOption->member_no }} -
                                                {{ $memberOption->last_name }},
                                                {{ $memberOption->first_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3" id="modal-guest-section" style="display:none;">

                                    <label for="modal_event_guest_id" class="form-label">
                                        Guest
                                    </label>

                                    <select name="event_guest_id" id="modal_event_guest_id" class="form-select">
                                        <option value="">Select Guest</option>

                                        @foreach ($availableGuests as $guestOption)
                                            @php
                                                $parentAttendee = $attendeesById->get($guestOption->event_attendee_id);
                                                $parentMember = $parentAttendee
                                                    ? $members->get($parentAttendee->member_id)
                                                    : null;
                                            @endphp

                                            <option value="{{ $guestOption->id }}">
                                                {{ $guestOption->guest_name }}
                                                @if ($parentMember)
                                                    - Guest of {{ $parentMember->member_no }}
                                                    {{ $parentMember->first_name }}
                                                    {{ $parentMember->last_name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                @if ($availableMembers->isEmpty() && $availableGuests->isEmpty())
                                    <div class="alert alert-warning mb-0">
                                        No unassigned confirmed members or guests are available.
                                    </div>
                                @endif
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Cancel
                                </button>

                                <button type="submit" class="btn btn-primary" id="modalAssignButton"
                                    @if ($availableMembers->isEmpty() && $availableGuests->isEmpty()) disabled @endif>
                                    Assign
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

{{-- ============================================================= --}}
{{-- CSS --}}
{{-- ============================================================= --}}
<style>
    .seating-layout-wrapper {
        width: 100%;
        overflow-x: auto;
        padding: 20px 0 35px 0;
    }

    .seating-layout {
        position: relative;
        width: 500px;
        height: 500px;
        margin: 0 auto;
    }

    .center-table {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 190px;
        height: 190px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background: #f4f6f9;
        border: 3px solid #6c757d;
        z-index: 1;
    }

    .center-table-round {
        border-radius: 50%;
    }

    .center-table-rectangle {
        width: 230px;
        height: 150px;
        border-radius: 10px;
    }

    .table-number {
        font-size: 22px;
        font-weight: 700;
        color: #343a40;
    }

    .table-count {
        margin-top: 5px;
        font-size: 15px;
        color: #6c757d;
    }

    .graphical-seat {
        position: absolute;
        width: 92px;
        height: 66px;
        transform: translate(-50%, -50%);
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        border: 2px solid transparent;
        transition: transform .15s ease, box-shadow .15s ease;
        z-index: 2;
        cursor: pointer;
    }

    .graphical-seat:hover {
        transform: translate(-50%, -50%) scale(1.07);
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
    }

    .seat-number {
        font-size: 18px;
        font-weight: 700;
        line-height: 20px;
    }

    .seat-person {
        font-size: 10px;
        margin-top: 4px;
        max-width: 84px;
        text-align: center;
        line-height: 12px;
    }

    .seat-available {
        background: #e9f2ff;
        border-color: #0d6efd;
        color: #0d6efd;
    }

    .seat-assigned {
        background: #e5f7ec;
        border-color: #198754;
        color: #146c43;
    }

    .seat-guest {
        background: #f3e8ff;
        border-color: #8b5cf6;
        color: #6d28d9;
    }

    .seat-blocked {
        background: #e9ecef;
        border-color: #6c757d;
        color: #495057;
    }

    .seat-legend {
        display: inline-block;
        width: 18px;
        height: 18px;
        border-radius: 4px;
        vertical-align: middle;
        margin-right: 6px;
        border-width: 2px;
        border-style: solid;
    }

    .seat-assignment-card {
        scroll-margin-top: 100px;
    }

    .seat-assignment-card:target {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 2px rgba(13, 110, 253, .15);
    }

    #assignSeatModal .select2-container {
        width: 100% !important;
    }

    #assignSeatModal .select2-selection--single {
        height: 38px;
    }

    #assignSeatModal .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }

    #assignSeatModal .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    @media (max-width: 767px) {
        .seating-layout-wrapper {
            overflow-x: auto;
        }

        .seating-layout {
            margin-left: auto;
            margin-right: auto;
        }
    }
</style>

{{-- ============================================================= --}}
{{-- SCRIPTS --}}
{{-- ============================================================= --}}
@section('scripts')
    <script>
        $(document).ready(function() {

            /* Seat Card - Member Select2 */
            $('.member-select').select2({
                width: '100%',
                placeholder: 'Select Member',
                allowClear: true
            });

            /* Seat Card - Guest Select2 */
            $('.guest-select').select2({
                width: '100%',
                placeholder: 'Select Guest',
                allowClear: true
            });

            /* Modal Member Select2 */
            $('#modal_member_id').select2({
                width: '100%',
                placeholder: 'Select Member',
                allowClear: true,
                dropdownParent: $('#assignSeatModal')
            });

            /* Modal Guest Select2 */
            $('#modal_event_guest_id').select2({
                width: '100%',
                placeholder: 'Select Guest',
                allowClear: true,
                dropdownParent: $('#assignSeatModal')
            });

            $('#modal_assignment_type').on('change', function() {
                updateAssignmentMode();
            });

            function updateAssignmentMode() {
                const type = $('#modal_assignment_type').val();
                const memberAction = $('#assignSeatForm').data('member-action');
                const guestAction = $('#assignSeatForm').data('guest-action');

                if (type === 'GUEST') {
                    $('#modal-member-section').hide();
                    $('#modal-guest-section').show();

                    $('#modal_member_id').prop('required', false);
                    $('#modal_event_guest_id').prop('required', true);

                    $('#assignSeatForm').attr('action', guestAction || '');
                    $('#modalAssignButton').text('Assign Guest');
                } else {
                    $('#modal-guest-section').hide();
                    $('#modal-member-section').show();

                    $('#modal_event_guest_id').prop('required', false);
                    $('#modal_member_id').prop('required', true);

                    $('#assignSeatForm').attr('action', memberAction || '');
                    $('#modalAssignButton').text('Assign Member');
                }
            }

            $('.assign-seat-btn').on('click', function() {
                const seatNo = $(this).data('seat-no');
                const memberAction = $(this).data('member-action');
                const guestAction = $(this).data('guest-action');

                $('#modalSeatLabel').val(
                    'Table {{ $table->table_no }} - Seat ' + seatNo
                );

                $('#assignSeatForm')
                    .data('member-action', memberAction)
                    .data('guest-action', guestAction);

                $('#modal_member_id')
                    .val(null)
                    .trigger('change');

                $('#modal_event_guest_id')
                    .val(null)
                    .trigger('change');

                @if ($availableMembers->isNotEmpty())
                    $('#modal_assignment_type').val('MEMBER');
                @elseif ($availableGuests->isNotEmpty())
                    $('#modal_assignment_type').val('GUEST');
                @endif

                updateAssignmentMode();

                const modalElement = document.getElementById('assignSeatModal');
                const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
            });

            $('#assignSeatModal').on('shown.bs.modal', function() {
                if ($('#modal_assignment_type').val() === 'GUEST') {
                    if ($('#modal_event_guest_id option').length > 1) {
                        $('#modal_event_guest_id').select2('open');
                    }
                } else {
                    if ($('#modal_member_id option').length > 1) {
                        $('#modal_member_id').select2('open');
                    }
                }
            });

            $('#assignSeatModal').on('hidden.bs.modal', function() {
                $('#modal_member_id')
                    .val(null)
                    .trigger('change');

                $('#modal_event_guest_id')
                    .val(null)
                    .trigger('change');

                $('#assignSeatForm')
                    .attr('action', '')
                    .removeData('member-action')
                    .removeData('guest-action');

                $('#modalSeatLabel').val('');

                @if ($availableMembers->isNotEmpty())
                    $('#modal_assignment_type').val('MEMBER');
                    $('#modal-member-section').show();
                    $('#modal-guest-section').hide();
                    $('#modalAssignButton').text('Assign Member');
                @elseif ($availableGuests->isNotEmpty())
                    $('#modal_assignment_type').val('GUEST');
                    $('#modal-member-section').hide();
                    $('#modal-guest-section').show();
                    $('#modalAssignButton').text('Assign Guest');
                @endif
            });
        });
    </script>
@endsection
