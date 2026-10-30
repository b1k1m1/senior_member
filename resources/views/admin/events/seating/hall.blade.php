@extends('admin.admin_dashboard')
@section('title', 'Hall Seating Layout')
@section('page-title', 'Hall Seating Layout')

@section('content')

    <div class="page-content">
        <div class="container-fluid">

            {{-- ============================================================= --}}
            {{-- PAGE HEADER --}}
            {{-- ============================================================= --}}

            <div class="row mb-3">

                <div class="col-md-7">

                    <h4 class="mb-1">
                        {{ $event->title }}
                    </h4>

                    <div class="text-muted">
                        Drag tables to arrange the hall layout.
                    </div>

                </div>


                <div class="col-md-5 text-md-end mt-2 mt-md-0">

                    <button type="button" id="saveHallLayout" class="btn btn-success me-2">

                        <i class="mdi mdi-content-save me-1"></i>

                        Save Layout

                    </button>


                    <a href="{{ route('events.seating.index', $event->id) }}" class="btn btn-secondary">

                        Back to Seating Plan

                    </a>

                </div>

            </div>


            {{-- ============================================================= --}}
            {{-- SAVE MESSAGE --}}
            {{-- ============================================================= --}}

            <div id="hallSaveMessage" class="alert d-none" role="alert">
            </div>


            {{-- ============================================================= --}}
            {{-- SUMMARY --}}
            {{-- ============================================================= --}}

            <div class="row mb-4">

                <div class="col-lg-3 col-md-6 mb-3">

                    <div class="card h-100">

                        <div class="card-body text-center">

                            <div class="text-muted">
                                Tables
                            </div>

                            <h3 class="mb-0">
                                {{ $totalTables }}
                            </h3>

                        </div>

                    </div>

                </div>


                <div class="col-lg-3 col-md-6 mb-3">

                    <div class="card h-100">

                        <div class="card-body text-center">

                            <div class="text-muted">
                                Total Seats
                            </div>

                            <h3 class="mb-0">
                                {{ $totalSeats }}
                            </h3>

                        </div>

                    </div>

                </div>


                <div class="col-lg-3 col-md-6 mb-3">

                    <div class="card h-100">

                        <div class="card-body text-center">

                            <div class="text-muted">
                                Assigned
                            </div>

                            <h3 class="mb-0 text-success">
                                {{ $assignedSeats }}
                            </h3>

                        </div>

                    </div>

                </div>


                <div class="col-lg-3 col-md-6 mb-3">

                    <div class="card h-100">

                        <div class="card-body text-center">

                            <div class="text-muted">
                                Available
                            </div>

                            <h3 class="mb-0 text-primary">
                                {{ $availableSeats }}
                            </h3>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ============================================================= --}}
            {{-- INSTRUCTIONS / LEGEND --}}
            {{-- ============================================================= --}}

            <div class="card mb-4">

                <div class="card-body">

                    <div
                        class="
                        d-flex
                        flex-wrap
                        align-items-center
                        justify-content-between
                        gap-3
                    ">

                        <div>

                            <strong>
                                Arrange Tables:
                            </strong>

                            <span class="text-muted ms-1">
                                Click and drag a table to move it.
                                Click Save Layout when finished.
                            </span>

                        </div>


                        <div class="d-flex flex-wrap gap-3">

                            <div>

                                <span class="hall-legend hall-empty"></span>

                                Empty

                            </div>

                            <div>

                                <span class="hall-legend hall-partial"></span>

                                Partial

                            </div>

                            <div>

                                <span class="hall-legend hall-full"></span>

                                Full

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ============================================================= --}}
            {{-- HALL --}}
            {{-- ============================================================= --}}

            <div class="card">

                <div
                    class="
                    card-header
                    d-flex
                    justify-content-between
                    align-items-center
                ">

                    <strong>
                        Event Hall
                    </strong>

                    <span class="text-muted small">

                        {{ $totalTables }} Tables

                    </span>

                </div>


                <div class="card-body">


                    {{-- Stage --}}
                    <div class="hall-stage">

                        STAGE

                    </div>


                    {{-- Hall Scroll Area --}}
                    <div class="hall-scroll-wrapper">

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | Hall Canvas Layout
                            |--------------------------------------------------------------------------
                            */

                            $defaultColumns = 5;

                            /*
                            |--------------------------------------------------------------------------
                            | Larger spacing between tables
                            |--------------------------------------------------------------------------
                            */

                            $horizontalSpacing = 300;

                            $verticalSpacing = 250;

                            $rowCount = max(1, (int) ceil(max($tables->count(), 1) / $defaultColumns));

                            /*
                            |--------------------------------------------------------------------------
                            | Large canvas gives user plenty of room to arrange tables
                            |--------------------------------------------------------------------------
                            */

                            $canvasHeight = max(900, $rowCount * $verticalSpacing + 250);
                        @endphp


                        <div id="hallCanvas" class="hall-canvas" style="height: {{ $canvasHeight }}px;">


                            @foreach ($tables as $index => $table)
                                @php

                                    /*
                                |--------------------------------------------------------------------------
                                | Seating Counts
                                |--------------------------------------------------------------------------
                                */

                                    $tableSeats = $seats->where('event_table_id', $table->id);

                                    $assigned = $tableSeats
                                        ->filter(function ($seat) {
                                            return !is_null($seat->member_id) || !is_null($seat->event_guest_id);
                                        })
                                        ->count();

                                    $capacity = (int) $table->capacity;

                                    /*
                                |--------------------------------------------------------------------------
                                | Status
                                |--------------------------------------------------------------------------
                                */

                                    if ($assigned === 0) {
                                        $tableStatusClass = 'hall-empty';
                                    } elseif ($assigned >= $capacity) {
                                        $tableStatusClass = 'hall-full';
                                    } else {
                                        $tableStatusClass = 'hall-partial';
                                    }

                                    /*
                                |--------------------------------------------------------------------------
                                | Default Position
                                |--------------------------------------------------------------------------
                                |
                                | Used only until administrator saves an actual
                                | position.
                                |
                                */

                                    $column = $index % $defaultColumns;

                                    $row = intdiv($index, $defaultColumns);

                                    $defaultX = 80 + $column * $horizontalSpacing;

                                    $defaultY = 80 + $row * $verticalSpacing;

                                    $positionX = $table->position_x !== null ? (int) $table->position_x : $defaultX;

                                    $positionY = $table->position_y !== null ? (int) $table->position_y : $defaultY;
                                @endphp


                                <div class="
                                    draggable-table
                                    {{ $tableStatusClass }}
                                "
                                    data-table-id="{{ $table->id }}"
                                    style="
                                    left: {{ $positionX }}px;
                                    top: {{ $positionY }}px;
                                ">


                                    {{-- Drag Handle --}}
                                    <div class="table-drag-handle">

                                        <i class="mdi mdi-drag"></i>

                                        Move

                                    </div>


                                    {{-- Table Shape --}}
                                    <div
                                        class="
                                        hall-table-shape

                                        {{ $table->table_shape === 'RECTANGLE' ? 'hall-table-rectangle' : 'hall-table-round' }}
                                    ">

                                        <div class="hall-table-number">

                                            {{ $table->table_no }}

                                        </div>

                                    </div>


                                    {{-- Table Name --}}
                                    <div class="hall-table-name">

                                        {{ $table->table_name ?: 'Table ' . $table->table_no }}

                                    </div>


                                    {{-- Assigned --}}
                                    <div class="hall-table-count">

                                        {{ $assigned }}
                                        /
                                        {{ $capacity }}

                                        Assigned

                                    </div>


                                    {{-- Available --}}
                                    <div class="hall-table-available">

                                        {{ max($capacity - $assigned, 0) }}

                                        Available

                                    </div>


                                    {{-- Open Table --}}
                                    <a href="{{ route('events.seating.table', [
                                        'eventId' => $event->id,
                                        'tableId' => $table->id,
                                    ]) }}"
                                        class="
                                        btn
                                        btn-sm
                                        btn-outline-primary
                                        mt-2
                                        open-table-btn
                                    ">

                                        View Seats

                                    </a>

                                </div>
                            @endforeach

                        </div>

                    </div>


                    {{-- Entrance --}}
                    <div class="hall-entrance">

                        ENTRANCE

                    </div>

                </div>

            </div>

        </div>
    </div>


    {{-- ============================================================= --}}
    {{-- CSS --}}
    {{-- ============================================================= --}}

    <style>
        /*
                    |--------------------------------------------------------------------------
                    | Stage
                    |--------------------------------------------------------------------------
                    */

        .hall-stage {
            width: 60%;
            max-width: 600px;

            margin: 0 auto 25px auto;

            padding: 14px 20px;

            text-align: center;

            background: #343a40;

            color: #ffffff;

            font-weight: 700;

            letter-spacing: 2px;

            border-radius: 6px;
        }


        /*
                    |--------------------------------------------------------------------------
                    | Hall Scroll Area
                    |--------------------------------------------------------------------------
                    */

        .hall-scroll-wrapper {
            width: 100%;

            overflow: auto;

            border: 1px solid #dee2e6;

            background: #fafafa;

            border-radius: 8px;

            padding: 15px;
        }


        /*
            | Hall Canvas
            |--------------------------------------------------------------------------
            |--------------------------------------------------------------------------
            */

        .hall-canvas {
            position: relative;

            width: 1800px;

            min-width: 1800px;

            background-color: #ffffff;

            background-image:
                linear-gradient(#f0f0f0 1px,
                    transparent 1px),
                linear-gradient(90deg,
                    #f0f0f0 1px,
                    transparent 1px);

            background-size: 25px 25px;

            border: 1px dashed #ced4da;

            border-radius: 6px;
        }


        /*
                    |--------------------------------------------------------------------------
                    | Draggable Table
                    |--------------------------------------------------------------------------
                    */

        .draggable-table {
            position: absolute;

            width: 180px;

            min-height: 165px;

            display: flex;

            flex-direction: column;

            align-items: center;

            justify-content: center;

            padding: 10px;

            border: 2px solid;

            border-radius: 12px;

            text-align: center;

            user-select: none;

            touch-action: none;

            cursor: grab;

            z-index: 2;

            transition:
                box-shadow .15s ease;
        }


        .draggable-table.dragging {
            cursor: grabbing;

            z-index: 100;

            box-shadow:
                0 10px 25px rgba(0, 0, 0, .20);
        }


        /*
                    |--------------------------------------------------------------------------
                    | Drag Handle
                    |--------------------------------------------------------------------------
                    */

        .table-drag-handle {
            width: 100%;

            margin-bottom: 5px;

            font-size: 11px;

            font-weight: 600;

            opacity: .65;

            cursor: grab;
        }


        /*
                    |--------------------------------------------------------------------------
                    | Table Shape
                    |--------------------------------------------------------------------------
                    */

        .hall-table-shape {
            width: 76px;

            height: 76px;

            display: flex;

            align-items: center;

            justify-content: center;

            margin-bottom: 6px;

            border: 3px solid currentColor;

            background: rgba(255,
                    255,
                    255,
                    .65);
        }


        .hall-table-round {
            border-radius: 50%;
        }


        .hall-table-rectangle {
            width: 92px;

            height: 62px;

            border-radius: 7px;
        }


        /*
                    |--------------------------------------------------------------------------
                    | Table Text
                    |--------------------------------------------------------------------------
                    */

        .hall-table-number {
            font-size: 22px;

            font-weight: 700;
        }


        .hall-table-name {
            font-size: 13px;

            font-weight: 700;

            line-height: 16px;
        }


        .hall-table-count {
            margin-top: 3px;

            font-size: 12px;

            font-weight: 600;
        }


        .hall-table-available {
            font-size: 11px;

            opacity: .85;
        }


        /*
                    |--------------------------------------------------------------------------
                    | Status Colors
                    |--------------------------------------------------------------------------
                    */

        .hall-empty {
            color: #6c757d;

            background: #f8f9fa;

            border-color: #adb5bd;
        }


        .hall-partial {
            color: #9a6700;

            background: #fff7db;

            border-color: #ffc107;
        }


        .hall-full {
            color: #146c43;

            background: #e5f7ec;

            border-color: #198754;
        }


        /*
                    |--------------------------------------------------------------------------
                    | Legend
                    |--------------------------------------------------------------------------
                    */

        .hall-legend {
            display: inline-block;

            width: 18px;

            height: 18px;

            border-radius: 4px;

            border: 2px solid;

            vertical-align: middle;

            margin-right: 5px;
        }


        /*
                    |--------------------------------------------------------------------------
                    | View Seats Button
                    |--------------------------------------------------------------------------
                    */

        .open-table-btn {
            cursor: pointer;

            touch-action: auto;
        }


        /*
                    |--------------------------------------------------------------------------
                    | Entrance
                    |--------------------------------------------------------------------------
                    */

        .hall-entrance {
            max-width: 350px;

            margin: 30px auto 0 auto;

            padding: 12px 15px;

            text-align: center;

            border: 2px dashed #6c757d;

            border-radius: 6px;

            color: #6c757d;

            font-weight: 700;

            letter-spacing: 2px;
        }


        /*
                    |--------------------------------------------------------------------------
                    | Responsive
                    |--------------------------------------------------------------------------
                    */

        @media (max-width: 767px) {

            .hall-stage {
                width: 90%;
            }

        }
    </style>

@endsection


{{-- ============================================================= --}}
{{-- SCRIPT --}}
{{-- ============================================================= --}}

@section('scripts')

    <script>
        $(document).ready(function() {

            const hallCanvas =
                document.getElementById('hallCanvas');


            let activeTable = null;

            let startPointerX = 0;

            let startPointerY = 0;

            let startTableX = 0;

            let startTableY = 0;


            /*
            |--------------------------------------------------------------------------
            | Start Drag
            |--------------------------------------------------------------------------
            */

            $('.draggable-table').on(
                'pointerdown',
                function(e) {

                    /*
                    |--------------------------------------------------------------------------
                    | Do Not Drag When Clicking View Seats
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $(e.target).closest(
                            '.open-table-btn'
                        ).length
                    ) {
                        return;
                    }


                    activeTable = this;


                    startPointerX =
                        e.clientX;


                    startPointerY =
                        e.clientY;


                    startTableX =
                        parseInt(
                            activeTable.style.left
                        ) || 0;


                    startTableY =
                        parseInt(
                            activeTable.style.top
                        ) || 0;


                    $(activeTable)
                        .addClass('dragging');


                    if (
                        activeTable.setPointerCapture
                    ) {

                        activeTable.setPointerCapture(
                            e.pointerId
                        );

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Drag
            |--------------------------------------------------------------------------
            */

            $('.draggable-table').on(
                'pointermove',
                function(e) {

                    if (
                        !activeTable ||
                        activeTable !== this
                    ) {
                        return;
                    }


                    const deltaX =
                        e.clientX -
                        startPointerX;


                    const deltaY =
                        e.clientY -
                        startPointerY;


                    let newX =
                        startTableX +
                        deltaX;


                    let newY =
                        startTableY +
                        deltaY;


                    /*
                    |--------------------------------------------------------------------------
                    | Keep Table Inside Canvas
                    |--------------------------------------------------------------------------
                    */

                    const maxX =
                        hallCanvas.clientWidth -
                        activeTable.offsetWidth;


                    const maxY =
                        hallCanvas.clientHeight -
                        activeTable.offsetHeight;


                    newX = Math.max(
                        0,
                        Math.min(
                            newX,
                            maxX
                        )
                    );


                    newY = Math.max(
                        0,
                        Math.min(
                            newY,
                            maxY
                        )
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Optional 5px Grid Snap
                    |--------------------------------------------------------------------------
                    */

                    newX =
                        Math.round(newX / 5) * 5;


                    newY =
                        Math.round(newY / 5) * 5;


                    activeTable.style.left =
                        newX + 'px';


                    activeTable.style.top =
                        newY + 'px';

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Stop Drag
            |--------------------------------------------------------------------------
            */

            $('.draggable-table').on(
                'pointerup pointercancel',
                function() {

                    if (!activeTable) {
                        return;
                    }


                    $(activeTable)
                        .removeClass('dragging');


                    activeTable = null;

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Save Hall Layout
            |--------------------------------------------------------------------------
            */

            $('#saveHallLayout').on(
                'click',
                function() {

                    const button = $(this);

                    const positions = [];


                    $('.draggable-table').each(
                        function() {

                            positions.push({

                                table_id: parseInt(
                                    $(this)
                                    .data('table-id')
                                ),

                                position_x: parseInt(
                                    this.style.left
                                ) || 0,

                                position_y: parseInt(
                                    this.style.top
                                ) || 0

                            });

                        }
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Disable Save Button
                    |--------------------------------------------------------------------------
                    */

                    button.prop(
                        'disabled',
                        true
                    );


                    button.html(
                        '<span class="spinner-border spinner-border-sm me-1"></span> Saving...'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Save Via AJAX
                    |--------------------------------------------------------------------------
                    */

                    fetch(
                            "{{ route('events.seating.hall.save', $event->id) }}", {

                                method: 'POST',

                                headers: {

                                    'Content-Type': 'application/json',

                                    'Accept': 'application/json',

                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'

                                },

                                body: JSON.stringify({
                                    positions: positions
                                })

                            }
                        )
                        .then(async response => {

                            const data =
                                await response.json();


                            if (!response.ok) {

                                throw new Error(
                                    data.message ||
                                    'Unable to save layout.'
                                );

                            }


                            return data;

                        })
                        .then(data => {

                            showHallMessage(
                                data.message ||
                                'Hall layout saved successfully.',
                                'success'
                            );

                        })
                        .catch(error => {

                            showHallMessage(
                                error.message ||
                                'Unable to save hall layout.',
                                'danger'
                            );

                        })
                        .finally(() => {

                            button.prop(
                                'disabled',
                                false
                            );


                            button.html(
                                '<i class="mdi mdi-content-save me-1"></i> Save Layout'
                            );

                        });

                }
            );


            /*
            |--------------------------------------------------------------------------
            | Message
            |--------------------------------------------------------------------------
            */

            function showHallMessage(
                message,
                type
            ) {

                const box =
                    $('#hallSaveMessage');


                box
                    .removeClass(
                        'd-none alert-success alert-danger'
                    )
                    .addClass(
                        'alert-' + type
                    )
                    .text(message);


                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

            }

        });
    </script>

@endsection
