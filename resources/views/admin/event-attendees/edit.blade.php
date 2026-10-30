@extends('admin.admin_dashboard')

@section('title', 'Edit Event Attendee')

@section('page-title', 'Edit Attendee - ' . $event->title)


@section('page-actions')

    <a href="{{ route('admin.event-attendees.index', ['event' => $event->id]) }}"
        class="btn btn-secondary">

        <i class="fas fa-arrow-left me-1"></i>

        Back to Attendees

    </a>

@endsection


@section('content')

    <div class="card">

        <div class="card-header">

            <h5 class="mb-0">
                Attendee Information
            </h5>

        </div>


        <div class="card-body">


            {{-- ============================================================= --}}
            {{-- VALIDATION ERRORS --}}
            {{-- ============================================================= --}}

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


            {{-- ============================================================= --}}
            {{-- MEMBER INFORMATION --}}
            {{-- ============================================================= --}}

            <div class="row mb-4">

                <div class="col-md-4">

                    <label class="text-muted">
                        Member No
                    </label>

                    <div class="fw-bold">

                        {{ $attendee->member?->member_no ?? '-' }}

                    </div>

                </div>


                <div class="col-md-4">

                    <label class="text-muted">
                        Member Name
                    </label>

                    <div class="fw-bold">

                        {{ $attendee->member?->first_name }}
                        {{ $attendee->member?->last_name }}

                    </div>

                </div>


                <div class="col-md-4">

                    <label class="text-muted">
                        Event
                    </label>

                    <div class="fw-bold">

                        {{ $event->title }}

                    </div>

                </div>

            </div>


            <hr>


            {{-- ============================================================= --}}
            {{-- EDIT FORM --}}
            {{-- ============================================================= --}}

            <form method="POST"
                action="{{ route('admin.event-attendees.update', [
                    'event' => $event->id,
                    'attendee' => $attendee->id,
                ]) }}">

                @csrf

                @method('PUT')


                <div class="row">


                    {{-- ===================================================== --}}
                    {{-- STATUS --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-4 mb-3">

                        <label for="status" class="form-label">

                            Status

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <select name="status" id="status" class="form-select" required>

                            <option value="TENTATIVE"
                                {{ old('status', $attendee->status) === 'TENTATIVE' ? 'selected' : '' }}>

                                Tentative

                            </option>


                            <option value="CONFIRMED"
                                {{ old('status', $attendee->status) === 'CONFIRMED' ? 'selected' : '' }}>

                                Confirmed

                            </option>


                            <option value="CANCELLED"
                                {{ old('status', $attendee->status) === 'CANCELLED' ? 'selected' : '' }}>

                                Cancelled

                            </option>

                        </select>


                        <small id="confirmationHelp" class="text-muted">

                            Payment is required before status can be Confirmed.

                        </small>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- GUEST COUNT --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-4 mb-3">

                        <label for="guests_count" class="form-label">

                            Guests

                        </label>


                        <input type="number" name="guests_count" id="guests_count" class="form-control" min="0"
                            max="{{ $event->max_guests_per_member ?? 0 }}"
                            value="{{ old('guests_count', $attendee->guests_count ?? 0) }}">


                        <small class="text-muted">

                            Maximum:
                            {{ $event->max_guests_per_member ?? 0 }}

                        </small>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- AMOUNT PAID --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-4 mb-3">

                        <label for="amount_paid" class="form-label">

                            Amount Paid

                        </label>


                        <div class="input-group">

                            <span class="input-group-text">
                                $
                            </span>


                            <input type="number" name="amount_paid" id="amount_paid" class="form-control" step="0.01"
                                min="0"
                                value="{{ old('amount_paid', number_format((float) $attendee->amount_paid, 2, '.', '')) }}">

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- PAYMENT DATE --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-4 mb-3">

                        <label for="payment_date" class="form-label">

                            Payment Date

                        </label>


                        <input type="date" name="payment_date" id="payment_date" class="form-control"
                            value="{{ old(
                                'payment_date',
                                $attendee->payment_date ? \Carbon\Carbon::parse($attendee->payment_date)->format('Y-m-d') : '',
                            ) }}">

                    </div>


                    {{-- ===================================================== --}}
                    {{-- RECEIPT --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-4 mb-3">

                        <label for="receipt_no" class="form-label">

                            Receipt No.

                        </label>


                        <input type="text" name="receipt_no" id="receipt_no" class="form-control" maxlength="30"
                            value="{{ old('receipt_no', $attendee->receipt_no) }}">

                    </div>


                    {{-- ===================================================== --}}
                    {{-- EVENT PRICE --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Event Price
                        </label>


                        <input type="text" class="form-control"
                            value="${{ number_format($event->price, 2) }}"
                            readonly>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- REMARKS --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-12 mb-3">

                        <label for="remarks" class="form-label">

                            Remarks

                        </label>


                        <textarea name="remarks" id="remarks" class="form-control" rows="4">{{ old('remarks', $attendee->remarks) }}</textarea>

                    </div>

                </div>


                {{-- ========================================================= --}}
                {{-- BUTTONS --}}
                {{-- ========================================================= --}}

                <div
                    class="
                    d-flex
                    justify-content-end
                    gap-2
                    mt-3
                ">


                    <a href="{{ route('admin.event-attendees.index', ['event' => $event->id]) }}"
                        class="btn btn-secondary">

                        Cancel

                    </a>


                    <button type="submit" class="btn btn-primary">

                        <i class="fas fa-save me-1"></i>

                        Update Attendee

                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection


@section('scripts')

    <script>
        $(function() {

            /*
            |--------------------------------------------------------------------------
            | Automatically Populate Payment Date
            |--------------------------------------------------------------------------
            */

            $('#amount_paid').on(
                'change input',
                function() {

                    const amount =
                        parseFloat(
                            $(this).val()
                        ) || 0;


                    if (
                        amount > 0 &&
                        !$('#payment_date').val()
                    ) {

                        const today =
                            new Date();


                        const year =
                            today.getFullYear();


                        const month =
                            String(
                                today.getMonth() + 1
                            ).padStart(
                                2,
                                '0'
                            );


                        const day =
                            String(
                                today.getDate()
                            ).padStart(
                                2,
                                '0'
                            );


                        $('#payment_date').val(
                            year +
                            '-' +
                            month +
                            '-' +
                            day
                        );

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Prevent Confirm Without Payment On Client Side
            |--------------------------------------------------------------------------
            */

            $('#status').on(
                'change',
                function() {

                    const status =
                        $(this).val();


                    const amount =
                        parseFloat(
                            $('#amount_paid').val()
                        ) || 0;


                    if (
                        status === 'CONFIRMED' &&
                        amount <= 0
                    ) {

                        alert(
                            'Please enter payment before setting attendee to Confirmed.'
                        );


                        $(this).val(
                            'TENTATIVE'
                        );

                    }

                }
            );

        });
    </script>

@endsection
