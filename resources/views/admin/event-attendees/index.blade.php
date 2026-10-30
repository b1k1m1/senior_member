@extends('admin.admin_dashboard')

@section('title', 'Event Attendees')
@section('page-title', 'Attendees - ' . $event->title)

@section('page-actions')

    <a href="{{ route('admin.events.index') }}" class="btn btn-secondary me-2">

        <i class="fas fa-arrow-left me-1"></i>
        Back to Events

    </a>

    <a href="{{ route('admin.event-attendees.create', ['event' => $event->id]) }}" class="btn btn-primary">

        <i class="fas fa-plus me-1"></i>
        Add Attendee

    </a>

@endsection


@section('content')

    {{-- ============================================================= --}}
    {{-- EVENT SUMMARY --}}
    {{-- ============================================================= --}}

    <div class="card mb-4">

        <div class="card-body">

            <div class="row">

                {{-- Event Date --}}
                <div class="col-md-3">

                    <div class="text-muted small">
                        Event Date
                    </div>

                    <strong>
                        {{ $event->start_date->format('F j, Y') }}
                        -
                        {{ $event->end_date->format('F j, Y') }}
                    </strong>

                </div>


                {{-- Status --}}
                <div class="col-md-3">

                    <div class="text-muted small">
                        Status
                    </div>

                    <span class="badge bg-primary">
                        {{ $event->status }}
                    </span>

                </div>


                {{-- Confirmed / Tentative --}}
                <div class="col-md-3">

                    <div class="text-muted small">
                        Confirmed / Tentative
                    </div>

                    <strong>
                        {{ $event->confirmed_count }}
                        /
                        {{ $event->tentative_count }}
                    </strong>

                </div>


                {{-- Capacity --}}
                <div class="col-md-3">

                    <div class="text-muted small">
                        Capacity
                    </div>

                    <strong>

                        @if ($event->capacity)
                            {{ $event->capacity }}
                            /
                            {{ $event->available_slots }}
                        @else
                            Unlimited
                        @endif

                    </strong>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================= --}}
    {{-- ATTENDEES TABLE --}}
    {{-- ============================================================= --}}

    <div class="card">

        <div class="card-body">

            <table id="attendees-table" class="table table-striped dt-responsive nowrap" style="width: 100%;">

                <thead>

                    <tr>

                        <th>Member No</th>

                        <th>Member Name</th>

                        <th>Status</th>

                        <th>Guests</th>

                        <th>Amount Paid</th>

                        <th>Payment Date</th>

                        <th style="min-width: 150px;">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>
                </tbody>

            </table>

        </div>

    </div>

@endsection


@section('scripts')

    <script>
        $(function() {

            /*
            |--------------------------------------------------------------------------
            | DataTable
            |--------------------------------------------------------------------------
            */

            $('#attendees-table').DataTable({

                processing: true,

                serverSide: true,


                ajax: {

                    url: '{{ route('admin.event-attendees.ajax', ['event' => $event->id]) }}',

                    type: 'GET'

                },


                columns: [

                    /*
                    |--------------------------------------------------------------------------
                    | Member No
                    |--------------------------------------------------------------------------
                    */

                    {
                        data: 'member_no',
                        name: 'member_no'
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Member Name
                    |--------------------------------------------------------------------------
                    */

                    {
                        data: 'member_name',
                        name: 'member_name'
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Status
                    |--------------------------------------------------------------------------
                    */

                    {
                        data: 'status',
                        name: 'status',

                        render: function(data) {

                            var badges = {

                                'CONFIRMED': 'bg-success',

                                'TENTATIVE': 'bg-warning',

                                'CANCELLED': 'bg-danger'

                            };


                            return '<span class="badge ' +
                                (badges[data] || 'bg-secondary') +
                                '">' +
                                data +
                                '</span>';
                        }
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Guests
                    |--------------------------------------------------------------------------
                    */

                    {
                        data: 'guests_count',
                        name: 'guests_count'
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Amount Paid
                    |--------------------------------------------------------------------------
                    */

                    {
                        data: 'amount_paid',
                        name: 'amount_paid',

                        render: function(data) {

                            if (
                                data === null ||
                                data === '' ||
                                typeof data === 'undefined'
                            ) {
                                return '-';
                            }

                            return '$' +
                                parseFloat(data).toFixed(2);
                        }
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Payment Date
                    |--------------------------------------------------------------------------
                    */

                    {
                        data: 'payment_date',
                        name: 'payment_date',

                        render: function(data) {

                            return data || '-';

                        }
                    },


                    /*
                    |--------------------------------------------------------------------------
                    | Actions
                    |--------------------------------------------------------------------------
                    */

                    {
                        data: null,
                        name: 'actions',

                        orderable: false,

                        searchable: false,

                        render: function(data) {

                            var buttons = '';


                            /*
                            |--------------------------------------------------------------------------
                            | Edit
                            |--------------------------------------------------------------------------
                            */

                            var editUrl =
                                '{{ route('admin.event-attendees.edit', [
                                    'event' => $event->id,
                                    'attendee' => ':id',
                                ]) }}'
                                .replace(
                                    ':id',
                                    data.id
                                );


                            buttons +=
                                '<a ' +
                                'href="' + editUrl + '" ' +
                                'class="btn btn-sm btn-primary me-1" ' +
                                'title="Edit Attendee">' +

                                '<i class="fas fa-edit"></i>' +

                                '</a>';

                            /* Guest */

                            if (parseInt(data.guests_count || 0) > 0) {

                                var guestUrl =
                                    '{{ route('admin.event-attendees.guests', [
                                        'event' => $event->id,
                                        'attendee' => ':id',
                                    ]) }}'
                                    .replace(':id', data.id);


                                buttons +=
                                    '<a ' +
                                    'href="' + guestUrl + '" ' +
                                    'class="btn btn-sm btn-info me-1" ' +
                                    'title="Manage Guests">' +

                                    '<i class="fas fa-users"></i>' +

                                    '</a>';
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Cancel Attendee
                            |--------------------------------------------------------------------------
                            */

                            if (data.status !== 'CANCELLED') {

                                buttons +=
                                    '<button ' +
                                    'type="button" ' +
                                    'class="btn btn-sm btn-outline-danger me-1" ' +
                                    'title="Cancel Attendee" ' +
                                    'onclick="updateAttendeeStatus(' +
                                    data.id +
                                    ', \'CANCELLED\')">' +

                                    '<i class="fas fa-times"></i>' +

                                    '</button>';
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Delete
                            |--------------------------------------------------------------------------
                            */

                            buttons +=
                                '<button ' +
                                'type="button" ' +
                                'class="btn btn-sm btn-danger" ' +
                                'title="Delete Attendee" ' +
                                'onclick="deleteAttendee(' +
                                data.id +
                                ')">' +

                                '<i class="fas fa-trash"></i>' +

                                '</button>';


                            return buttons;
                        }
                    }

                ],


                /*
                |--------------------------------------------------------------------------
                | DataTable Options
                |--------------------------------------------------------------------------
                */

                lengthMenu: [

                    [10, 25, 50, 100],

                    [10, 25, 50, 100]

                ],

                pageLength: 10

            });

        });


        /*
        |--------------------------------------------------------------------------
        | Cancel / Update Attendee Status
        |--------------------------------------------------------------------------
        */

        function updateAttendeeStatus(id, status) {
            var message = '';


            if (status === 'CANCELLED') {

                message =
                    'Cancel this attendee?';

            } else if (status === 'TENTATIVE') {

                message =
                    'Change this attendee to tentative?';

            } else if (status === 'CONFIRMED') {

                message =
                    'Confirm this attendee?';

            }


            if (!confirm(message)) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Build URL
            |--------------------------------------------------------------------------
            */

            var url =
                '{{ route('admin.event-attendees.update-status', [
                    'event' => $event->id,
                    'attendee' => ':id',
                ]) }}';


            url =
                url.replace(
                    ':id',
                    id
                );


            /*
            |--------------------------------------------------------------------------
            | AJAX
            |--------------------------------------------------------------------------
            */

            $.ajax({

                url: url,

                type: 'PUT',

                data: {

                    status: status,

                    _token: '{{ csrf_token() }}'

                },


                success: function(response) {

                    $('#attendees-table')
                        .DataTable()
                        .ajax
                        .reload(null, false);

                },


                error: function(xhr) {

                    var message =
                        'Unable to update attendee status.';


                    if (
                        xhr.responseJSON &&
                        xhr.responseJSON.message
                    ) {

                        message =
                            xhr.responseJSON.message;

                    }


                    alert(message);

                }

            });
        }


        /*
        |--------------------------------------------------------------------------
        | Delete Attendee
        |--------------------------------------------------------------------------
        */

        function deleteAttendee(id) {
            deleteItem(

                '{{ route('admin.event-attendees.destroy', [
                    'event' => $event->id,
                    'attendee' => ':id',
                ]) }}'
                .replace(
                    ':id',
                    id
                ),

                id
            );
        }
    </script>

@endsection
