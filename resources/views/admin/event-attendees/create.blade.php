@extends('admin.admin_dashboard')

@section('title', 'Add Attendee')
@section('page-title', 'Add Attendee - ' . $event->title)

@section('page-actions')

    <a href="{{ route('admin.event-attendees.index', ['event' => $event->id]) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i>
        Back
    </a>

@endsection


@section('styles')

    <style>
        .select2-container {
            width: 100% !important;
        }

        .spouse-box {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 15px;
            background: #f8f9fa;
        }

        .spouse-name {
            font-weight: 600;
        }
    </style>

@endsection


@section('content')

    <div class="card">

        <div class="card-body">

            <form action="{{ route('admin.event-attendees.store', $event->id) }}" method="POST">

                @csrf


                <div class="row">


                    {{-- ===================================================== --}}
                    {{-- MEMBER --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Member *
                        </label>

                        <select name="member_id" id="member-select" class="form-select" required>
                            <option value="">
                                Search Member...
                            </option>
                        </select>

                        @error('member_id')
                            <div class="text-danger">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- ===================================================== --}}
                    {{-- STATUS --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Status *
                        </label>

                        <select name="status" class="form-select" required>

                            <option value="TENTATIVE" {{ old('status') == 'TENTATIVE' ? 'selected' : '' }}>
                                Tentative
                            </option>

                            <option value="CONFIRMED" {{ old('status') == 'CONFIRMED' ? 'selected' : '' }}>
                                Confirmed
                            </option>

                            <option value="CANCELLED" {{ old('status') == 'CANCELLED' ? 'selected' : '' }}>
                                Cancelled
                            </option>

                        </select>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- ASSOCIATED SPOUSE --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-12 mb-3" id="spouse-section" style="display: none;">

                        <div class="spouse-box">

                            <div
                                class="
                                d-flex
                                justify-content-between
                                align-items-center
                                flex-wrap
                                gap-2
                            ">

                                <div>

                                    <div class="text-muted small mb-1">
                                        Associated Spouse
                                    </div>

                                    <div id="spouse-name" class="spouse-name">
                                    </div>

                                    <div id="spouse-member-no" class="small text-muted">
                                    </div>

                                </div>


                                <div id="spouse-checkbox-area">

                                    <div class="form-check">

                                        <input type="checkbox" name="spouse_attending" id="spouse_attending"
                                            class="form-check-input" value="1">

                                        <label for="spouse_attending" class="form-check-label">
                                            Spouse Attending
                                        </label>

                                    </div>

                                </div>

                            </div>


                            <div id="spouse-already-registered" class="alert alert-warning mt-3 mb-0"
                                style="display: none;">
                                This spouse is already registered for this event.
                            </div>


                            <input type="hidden" name="spouse_member_id" id="spouse_member_id" value="">

                        </div>

                    </div>


                    {{-- ===================================================== --}}
                    {{-- GUEST COUNT --}}
                    {{-- ===================================================== --}}

                    @if ($event->attendance_type == 'MEMBERS_WITH_GUESTS')
                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Number of Guests
                            </label>

                            <input type="number" name="guests_count" class="form-control" min="0"
                                max="{{ $event->max_guests_per_member ?? '' }}" value="{{ old('guests_count', 0) }}">

                        </div>
                    @endif


                    {{-- ===================================================== --}}
                    {{-- AMOUNT PAID --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label" id="amount-paid-label">
                            Amount Paid
                        </label>

                        <input type="number" name="amount_paid" id="amount_paid" class="form-control" min="0"
                            step="0.01" value="{{ old('amount_paid', 0) }}">

                    </div>

                    {{-- ============================================================= --}}
                    {{-- SPOUSE AMOUNT PAID - SEPARATE CHARGE ONLY --}}
                    {{-- ============================================================= --}}

                    <div class="col-md-6 mb-3" id="spouse-payment-section" style="display: none;">

                        <label class="form-label">
                            Spouse Amount Paid
                        </label>

                        <input type="number" name="spouse_amount_paid" id="spouse_amount_paid" class="form-control"
                            min="0" step="0.01" value="{{ old('spouse_amount_paid', 0) }}">

                        @error('spouse_amount_paid')
                            <div class="text-danger">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- ===================================================== --}}
                    {{-- PAYMENT DATE --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Payment Date
                        </label>

                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date') }}">

                    </div>


                    {{-- ===================================================== --}}
                    {{-- RECEIPT NO --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Receipt No
                        </label>

                        <input type="text" name="receipt_no" class="form-control" value="{{ old('receipt_no') }}">

                    </div>


                    {{-- ===================================================== --}}
                    {{-- REMARKS --}}
                    {{-- ===================================================== --}}

                    <div class="col-md-12 mb-3">

                        <label class="form-label">
                            Remarks
                        </label>

                        <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>

                    </div>

                </div>


                {{-- ========================================================= --}}
                {{-- BUTTONS --}}
                {{-- ========================================================= --}}

                <div class="d-flex justify-content-end gap-2">

                    <a href="{{ route('admin.event-attendees.index', ['event' => $event->id]) }}"
                        class="btn btn-secondary">
                        Cancel
                    </a>


                    <button type="submit" class="btn btn-primary">
                        Save
                    </button>

                </div>

            </form>

        </div>

    </div>

@endsection


@section('scripts')

    <script>
        $(document).ready(function() {

            var spouseChargeMode =
                '{{ $event->spouse_charge_mode }}';


            $('#spouse_attending').on(
                'change',
                function() {

                    updateSpousePaymentFields();

                }
            );


            function updateSpousePaymentFields() {
                var spouseAttending =
                    $('#spouse_attending')
                    .is(':checked');


                /*
                |--------------------------------------------------------------------------
                | No Spouse Attending
                |--------------------------------------------------------------------------
                */

                if (!spouseAttending) {

                    $('#spouse-payment-section')
                        .hide();


                    $('#amount-paid-label')
                        .text(
                            'Amount Paid'
                        );


                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Separate Charge
                |--------------------------------------------------------------------------
                */

                if (
                    spouseChargeMode === 'SEPARATE'
                ) {

                    $('#amount-paid-label')
                        .text(
                            'Member Amount Paid'
                        );


                    $('#spouse-payment-section')
                        .show();

                }


                /*
                |--------------------------------------------------------------------------
                | Combined Couple Charge
                |--------------------------------------------------------------------------
                */
                else {

                    $('#amount-paid-label')
                        .text(
                            'Combined Couple Amount Paid'
                        );


                    $('#spouse-payment-section')
                        .hide();


                    $('#spouse_amount_paid')
                        .val(0);

                }
            }

            /*
            |--------------------------------------------------------------------------
            | Member Select2
            |--------------------------------------------------------------------------
            */



            $('#member-select').select2({

                ajax: {

                    url: '{{ route('admin.members.search') }}',

                    dataType: 'json',

                    delay: 250,

                    processResults: function(data) {

                        return {
                            results: data.results
                        };

                    },

                    cache: true
                },

                minimumInputLength: 2,

                placeholder: 'Search by name, member no, or email',

                allowClear: true

            });


            /*
            |--------------------------------------------------------------------------
            | Member Selected
            |--------------------------------------------------------------------------
            */

            $('#member-select').on(
                'change',
                function() {

                    var memberId =
                        $(this).val();


                    /*
                    |--------------------------------------------------------------------------
                    | Reset Spouse Area
                    |--------------------------------------------------------------------------
                    */

                    resetSpouseSection();


                    if (!memberId) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Build Spouse Lookup URL
                    |--------------------------------------------------------------------------
                    */

                    var spouseUrl =
                        '{{ route('admin.event-attendees.find-spouse', [
                            'event' => $event->id,
                            'member' => ':member',
                        ]) }}';


                    spouseUrl =
                        spouseUrl.replace(
                            ':member',
                            memberId
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | Lookup Spouse
                    |--------------------------------------------------------------------------
                    */

                    $.ajax({

                        url: spouseUrl,

                        type: 'GET',

                        dataType: 'json',


                        success: function(response) {

                            /*
                            |--------------------------------------------------------------------------
                            | No Spouse Found
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !response.found ||
                                !response.spouse
                            ) {

                                resetSpouseSection();

                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Populate Spouse
                            |--------------------------------------------------------------------------
                            */

                            $('#spouse_member_id')
                                .val(
                                    response.spouse.id
                                );


                            $('#spouse-name')
                                .text(
                                    response.spouse.full_name
                                );


                            $('#spouse-member-no')
                                .text(
                                    'Member No: ' +
                                    response.spouse.member_no
                                );


                            /*
                            |--------------------------------------------------------------------------
                            | Already Registered
                            |--------------------------------------------------------------------------
                            */

                            if (
                                response.spouse.already_registered
                            ) {

                                $('#spouse_attending')
                                    .prop(
                                        'checked',
                                        false
                                    )
                                    .prop(
                                        'disabled',
                                        true
                                    );


                                $('#spouse-checkbox-area')
                                    .hide();


                                $('#spouse-already-registered')
                                    .show();

                            } else {

                                $('#spouse_attending')
                                    .prop(
                                        'disabled',
                                        false
                                    );


                                $('#spouse-checkbox-area')
                                    .show();


                                $('#spouse-already-registered')
                                    .hide();

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Show Spouse Section
                            |--------------------------------------------------------------------------
                            */

                            $('#spouse-section')
                                .slideDown();

                        },


                        error: function() {

                            resetSpouseSection();

                        }

                    });

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Reset Spouse Section
            |--------------------------------------------------------------------------
            */

            function resetSpouseSection() {
                $('#spouse_member_id')
                    .val('');


                $('#spouse-name')
                    .text('');


                $('#spouse-member-no')
                    .text('');


                $('#spouse_attending')
                    .prop(
                        'checked',
                        false
                    )
                    .prop(
                        'disabled',
                        false
                    );

                $('#spouse-payment-section')
                    .hide();


                $('#spouse_amount_paid')
                    .val(0);


                $('#amount-paid-label')
                    .text('Amount Paid');


                $('#spouse-checkbox-area')
                    .show();


                $('#spouse-already-registered')
                    .hide();


                $('#spouse-section')
                    .hide();
            }

        });
    </script>

@endsection
