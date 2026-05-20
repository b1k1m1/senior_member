<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $receiptNumber }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
            border-bottom: 3px solid #1e3a5f;
            padding-bottom: 15px;
        }
        .founder-section {
            width: 20%;
            text-align: center;
        }
        .founder-section img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #1e3a5f;
        }
        .founder-section .name {
            font-size: 10px;
            font-weight: bold;
            color: #1e3a5f;
            margin-top: 5px;
        }
        .org-section {
            width: 55%;
            text-align: center;
            padding: 0 15px;
        }
        .org-section .org-name {
            font-size: 20px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 10px;
        }
        .org-section .org-details {
            font-size: 10px;
            color: #555;
            line-height: 1.8;
        }
        .logo-section {
            width: 20%;
            text-align: right;
        }
        .logo-section img {
            width: 90px;
            height: 90px;
            object-fit: contain;
        }
        .content-section {
            display: flex;
            margin-top: 20px;
        }
        .left-section {
            width: 35%;
            border-right: 1px solid #ddd;
            padding-right: 15px;
        }
        .right-section {
            width: 65%;
            padding-left: 15px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1e3a5f;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 1px solid #1e3a5f;
            padding-bottom: 3px;
        }
        .office-bearer {
            margin-bottom: 12px;
        }
        .office-bearer .name {
            font-weight: bold;
            font-size: 11px;
        }
        .office-bearer .title {
            font-size: 9px;
            color: #666;
        }
        .welcome-box {
            background-color: #f8f9fa;
            border-left: 4px solid #1e3a5f;
            padding: 15px;
            margin-bottom: 20px;
        }
        .welcome-box p {
            font-size: 11px;
            color: #555;
            font-style: italic;
        }
        .receipt-box {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
        }
        .receipt-title {
            font-size: 14px;
            font-weight: bold;
            color: #1e3a5f;
            text-align: center;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .receipt-details {
            width: 100%;
        }
        .receipt-details tr {
            border-bottom: 1px solid #eee;
        }
        .receipt-details tr:last-child {
            border-bottom: none;
        }
        .receipt-details td {
            padding: 8px 5px;
        }
        .receipt-details td.label {
            font-weight: bold;
            width: 40%;
            color: #555;
        }
        .receipt-details td.value {
            text-align: right;
        }
        .total-row {
            background-color: #1e3a5f;
            color: white;
        }
        .total-row td {
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mt-2 { margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header">
            <div class="founder-section">
                @if($org['founder_photo_base64'])
                <img src="{{ $org['founder_photo_base64'] }}" alt="Founder">
                @else
                <div style="width:70px;height:70px;background:#ddd;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;">
                    <span style="font-size:24px;color:#999;">👤</span>
                </div>
                @endif
                <div class="name">{{ $org['founder_name'] }}</div>
            </div>
            
            <div class="org-section">
                <div class="org-name">{{ $org['name'] }}</div>
                <div class="org-details">
                    {{ $org['address'] }}<br>
                    Phone: {{ $org['phone'] }} | Email: {{ $org['email'] }}<br>
                    Tax ID: {{ $org['tax_id'] }} | Reg. No: {{ $org['registration_no'] }}
                </div>
            </div>
            
            <div class="logo-section">
                @if($org['logo_base64'])
                <img src="{{ $org['logo_base64'] }}" alt="Logo">
                @endif
            </div>
        </div>
        
        <!-- Content Section -->
        <div class="content-section">
            <!-- Left: Office Bearers -->
            <div class="left-section">
                <div class="section-title">Current Office Bearers</div>
                
                @foreach($org['office_bearers'] as $bearer)
                <div class="office-bearer">
                    <div class="name">{{ $bearer['name'] }}</div>
                    <div class="title">{{ $bearer['title'] }}</div>
                </div>
                @endforeach
            </div>
            
            <!-- Right: Welcome Message & Receipt -->
            <div class="right-section">
                <div class="welcome-box">
                    <p>{{ $org['welcome_message'] }}</p>
                </div>
                
                <div class="receipt-box">
                    <div class="receipt-title">Official Receipt</div>
                    <table class="receipt-details">
                        <tr>
                            <td class="label">Receipt No:</td>
                            <td class="value">{{ $receiptNumber }}</td>
                        </tr>
                        <tr>
                            <td class="label">Date:</td>
                            <td class="value">{{ $payment->payment_date->format('F j, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Member No:</td>
                            <td class="value">{{ $member->member_no }}</td>
                        </tr>
                        <tr>
                            <td class="label">Member Name:</td>
                            <td class="value">{{ $member->first_name }} {{ $member->last_name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Membership Type:</td>
                            <td class="value">{{ $member->membershipType?->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Payment For:</td>
                            <td class="value">{{ $payment->payment_for ?? 'Membership Dues' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Payment Method:</td>
                            <td class="value">{{ $payment->payment_method ?? 'Cash' }}</td>
                        </tr>
                        @if($payment->receipt_no)
                        <tr>
                            <td class="label">Ref. No:</td>
                            <td class="value">{{ $payment->receipt_no }}</td>
                        </tr>
                        @endif
                        <tr class="total-row">
                            <td class="label">Amount Paid:</td>
                            <td class="value">{{ $org['currency_symbol'] }}{{ number_format($payment->amount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>This is a computer-generated document. No signature required.</p>
            <p>{{ $org['name'] }} | {{ $org['website'] }}</p>
        </div>
    </div>
</body>
</html>
