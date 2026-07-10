<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $receipt->receipt_no }}</title>
    <style>
    @page {
        size: A4 portrait;
        margin: 10mm;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 11px;
        line-height: 1.25;
        color: #333;
        background: #fff;
    }

    .container {
        width: 100%;
        max-width: 720px;
        margin: 0 auto;
        padding: 5px;
    }

    .header {
        width: 100%;
        border-bottom: 2px solid #1e3a5f;
        padding-bottom: 8px;
        margin-bottom: 10px;
        display: table;
    }

    .logo-section {
        display: table-cell;
        width: 90px;
        vertical-align: top;
        text-align: left;
    }

    .logo-section img {
        max-width: 70px;
        max-height: 60px;
        object-fit: contain;
    }

    .org-section {
        display: table-cell;
        vertical-align: top;
        text-align: center;
        padding: 0 8px;
    }

    .org-name {
        font-size: 17px;
        font-weight: bold;
        color: #1e3a5f;
        margin-bottom: 4px;
        text-transform: uppercase;
    }

    .org-details {
        font-size: 10px;
        color: #555;
        line-height: 1.35;
    }

    .receipt-box {
        border: 2px solid #1e3a5f;
        border-radius: 5px;
        padding: 16px;
        margin-top: 12px;
        min-height: 165mm;
    }

    .receipt-title {
        font-size: 16px;
        font-weight: bold;
        color: #1e3a5f;
        text-align: center;
        margin-bottom: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .receipt-details {
        width: 100%;
        border-collapse: collapse;
    }

    .receipt-details td {
        padding: 5px 6px;
        border-bottom: 1px solid #e5e5e5;
        vertical-align: top;
    }

    .receipt-details tr:last-child td {
        border-bottom: none;
    }

    .receipt-details td.label {
        width: 32%;
        font-weight: bold;
        color: #444;
    }

    .receipt-details td.value {
        width: 68%;
        text-align: left;
        color: #222;
    }

    .amount-row td {
        background-color: #1e3a5f;
        color: #fff !important;
        font-weight: bold;
        font-size: 13px;
        border-bottom: none;
        padding: 6px;
    }

    .amount-row td.value {
        text-align: right;
    }

    .remarks-box {
        margin-top: 10px;
        border: 1px solid #ddd;
        padding: 8px;
        min-height: 35px;
        background: #fafafa;
    }

    .remarks-title {
        font-weight: bold;
        color: #1e3a5f;
        margin-bottom: 3px;
    }

    .footer {
        margin-top: 12px;
        text-align: center;
        font-size: 9px;
        color: #777;
        border-top: 1px solid #ddd;
        padding-top: 6px;
        line-height: 1.25;
    }

    .print-button-area {
        text-align: right;
        margin-bottom: 8px;
    }

    .print-button {
        background: #1e3a5f;
        color: #fff;
        border: none;
        padding: 6px 12px;
        font-size: 12px;
        cursor: pointer;
        border-radius: 4px;
    }

    @media print {
        html,
        body {
            width: 210mm;
            min-height: 297mm;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        body {
            font-size: 10.5px;
            line-height: 1.2;
        }

        .container {
            max-width: 100%;
            padding: 0;
            margin: 0;
        }

        .print-button-area {
            display: none;
        }

        .receipt-box {
            page-break-inside: avoid;
        }

        .header,
        .footer {
            page-break-inside: avoid;
        }
    }
    </style>
    </head>

<body>

<div class="container">

    <div class="print-button-area">
        <button type="button" class="print-button" onclick="window.print();">
            Print Receipt
        </button>
    </div>

    {{-- Header --}}
    <div class="header">

        <div class="logo-section">
            @if($org && !empty($org->logo) && file_exists(public_path('storage/' . $org->logo)))
                <img src="{{ asset('storage/' . $org->logo) }}" alt="Logo">
            @endif
        </div>

        <div class="org-section">
            <div class="org-name">
                {{ $org->name ?? 'Organization Name' }}
            </div>

            <div class="org-details">
                @if(!empty($org->address))
                    {{ $org->address }}<br>
                @endif

                @if(!empty($org->phone) || !empty($org->email))
                    @if(!empty($org->phone))
                        Phone: {{ $org->phone }}
                    @endif

                    @if(!empty($org->phone) && !empty($org->email))
                        |
                    @endif

                    @if(!empty($org->email))
                        Email: {{ $org->email }}
                    @endif

                    <br>
                @endif

                @if(!empty($org->tax_id) || !empty($org->registration_no))
                    @if(!empty($org->tax_id))
                        Tax ID: {{ $org->tax_id }}
                    @endif

                    @if(!empty($org->tax_id) && !empty($org->registration_no))
                        |
                    @endif

                    @if(!empty($org->registration_no))
                        Reg. No: {{ $org->registration_no }}
                    @endif
                @endif
            </div>
        </div>

        <div class="logo-section"></div>

    </div>

    {{-- Receipt Details --}}
    <div class="receipt-box">

        <div class="receipt-title">
            Official Receipt
        </div>

        <table class="receipt-details">

            <tr>
                <td class="label">Receipt No:</td>
                <td class="value">{{ $receipt->receipt_no }}</td>
            </tr>

            <tr>
                <td class="label">Receipt Date:</td>
                <td class="value">
                    {{ $receipt->created_at ? $receipt->created_at->format('F j, Y') : '' }}
                </td>
            </tr>

            @if($receipt->receiptType)
                <tr>
                    <td class="label">Receipt Type:</td>
                    <td class="value">{{ $receipt->receiptType->name }}</td>
                </tr>
            @endif

            <tr>
                <td class="label">Received From:</td>
                <td class="value">{{ $receipt->received_from }}</td>
            </tr>

            @if($receipt->address1 || $receipt->address2 || $receipt->city || $receipt->state || $receipt->zip)
                <tr>
                    <td class="label">Address:</td>
                    <td class="value">
                        @if($receipt->address1)
                            {{ $receipt->address1 }}<br>
                        @endif

                        @if($receipt->address2)
                            {{ $receipt->address2 }}<br>
                        @endif

                        @if($receipt->city || $receipt->state || $receipt->zip)
                            {{ $receipt->city }}
                            @if($receipt->city && $receipt->state), @endif
                            {{ $receipt->state }} {{ $receipt->zip }}
                        @endif

                        @if($receipt->county)
                            <br>County: {{ $receipt->county }}
                        @endif
                    </td>
                </tr>
            @endif

            <tr>
                <td class="label">Payment Mode:</td>
                <td class="value">
                    {{ str_replace('_', ' ', $receipt->payment_mode) }}
                </td>
            </tr>

            @if($receipt->payment_mode === 'CHECK')
                @if($receipt->bank_name)
                    <tr>
                        <td class="label">Bank Name:</td>
                        <td class="value">{{ $receipt->bank_name }}</td>
                    </tr>
                @endif

                @if($receipt->check_number)
                    <tr>
                        <td class="label">Check Number:</td>
                        <td class="value">{{ $receipt->check_number }}</td>
                    </tr>
                @endif

                @if($receipt->check_date)
                    <tr>
                        <td class="label">Check Date:</td>
                        <td class="value">{{ $receipt->check_date->format('F j, Y') }}</td>
                    </tr>
                @endif
            @endif

            @if($receipt->donor_name)
                <tr>
                    <td class="label">Donor Name:</td>
                    <td class="value">{{ $receipt->donor_name }}</td>
                </tr>
            @endif

            <tr class="amount-row">
                <td class="label">Amount Received:</td>
                <td class="value">${{ number_format((float) $receipt->amount, 2) }}</td>
            </tr>

        </table>

        @if($receipt->remarks)
            <div class="remarks-box">
                <div class="remarks-title">Remarks:</div>
                <div>{{ $receipt->remarks }}</div>
            </div>
        @endif

    </div>

    {{-- Footer --}}
    <div class="footer">
    <p>
        This is a computer-generated receipt.
        @if($org && !empty($org->name))
            {{ $org->name }}
        @endif
        @if($org && !empty($org->website))
            | {{ $org->website }}
        @endif
    </p>
</div>

</div>

<script>
    window.onload = function () {
        window.print();
    };
</script>

</body>
</html>
