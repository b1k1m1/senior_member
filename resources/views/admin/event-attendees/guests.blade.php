@extends('admin.admin_dashboard')

@section('title', 'Manage Guests')
@section('page-title', 'Manage Guests - ' . $event->title)

@section('page-actions')

    <a href="{{ route('admin.event-attendees.index', ['event' => $event->id]) }}"
        class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        Back to Attendees
    </a>

@endsection


@section('content')

    <div class="card mb-4">

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">

                    <div class="text-muted small">
                        Member No
                    </div>

                    <strong>
                        {{ $attendee->member?->member_no ?? '-' }}
                    </strong>

                </div>


                <div class="col-md-4 mb-3">

                    <div class="text-muted small">
                        Member Name
                    </div>

                    <strong>
                        {{ $attendee->member?->first_name }}
                        {{ $attendee->member?->last_name }}
                    </strong>

                </div>


                <div class="col-md-4 mb-3">

                    <div class="text-muted small">
                        Number of Guests
                    </div>

                    <strong>
                        {{ $attendee->guests_count }}
                    </strong>

                </div>

            </div>

        </div>

    </div>


    <div class="card">

        <div class="card-header">

            <h5 class="mb-0">
                Guest Information
            </h5>

        </div>


        <div class="card-body">


            @if ($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach ($errors->all() as $error)
                            <li>
                                {{ $error }}
                            </li>
                        @endforeach

                    </ul>

                </div>

            @endif


            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif


            <form method="POST"
                action="{{ route('admin.event-attendees.guests.store', [
                    'event' => $event->id,
                    'attendee' => $attendee->id,
                ]) }}">

                @csrf


                @for ($i = 0; $i < (int) $attendee->guests_count; $i++)
                    @php

                        $existingGuest = $guests->get($i);

                        $guestName = old('guests.' . $i . '.guest_name', $existingGuest->guest_name ?? '');

                        $guestType = old('guests.' . $i . '.guest_type', $existingGuest->guest_type ?? 'GUEST');

                        $remarks = old('guests.' . $i . '.remarks', $existingGuest->remarks ?? '');

                    @endphp


                    <div class="border rounded p-3 mb-3">

                        <h6 class="mb-3">

                            Guest {{ $i + 1 }}

                        </h6>


                        <div class="row">


                            {{-- Guest Name --}}
                            <div class="col-md-5 mb-3">

                                <label class="form-label">
                                    Guest Name *
                                </label>

                                <input type="text" name="guests[{{ $i }}][guest_name]" class="form-control"
                                    maxlength="120" required value="{{ $guestName }}">

                            </div>


                            {{-- Guest Type --}}
                            <div class="col-md-3 mb-3">

                                <label class="form-label">
                                    Guest Type *
                                </label>

                                <select name="guests[{{ $i }}][guest_type]" class="form-select" required>

                                    <option value="FAMILY" {{ $guestType === 'FAMILY' ? 'selected' : '' }}>
                                        Family
                                    </option>

                                    <option value="GUEST" {{ $guestType === 'GUEST' ? 'selected' : '' }}>
                                        Guest
                                    </option>

                                </select>

                            </div>


                            {{-- Remarks --}}
                            <div class="col-md-4 mb-3">

                                <label class="form-label">
                                    Remarks
                                </label>

                                <input type="text" name="guests[{{ $i }}][remarks]" class="form-control"
                                    maxlength="255" value="{{ $remarks }}">

                            </div>

                        </div>

                    </div>
                @endfor


                <div class="d-flex justify-content-end gap-2">

                    <a href="{{ route('admin.event-attendees.index', ['event' => $event->id]) }}"
                        class="btn btn-secondary">
                        Cancel
                    </a>


                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Save Guests
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection
