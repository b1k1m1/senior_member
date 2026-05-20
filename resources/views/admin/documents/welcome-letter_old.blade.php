<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome Letter - {{ $member->member_no }}</title>
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
        .logo-section img {
            width: 90px;
            height: 90px;
            object-fit: contain;
        }
        .content-section {
            margin-top: 30px;
        }
        .member-info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .member-info table {
            width: 100%;
        }
        .member-info td {
            padding: 8px 10px;
        }
        .member-info td.label {
            font-weight: bold;
            width: 40%;
            color: #555;
        }
        .welcome-text {
            margin: 30px 0;
            text-align: justify;
        }
        .welcome-text p {
            margin-bottom: 15px;
        }
        .signature {
            margin-top: 50px;
        }
        .signature-line {
            border-top: 1px solid #333;
            width: 250px;
            margin-top: 50px;
            padding-top: 5px;
            text-align: center;
        }
        .signature-name {
            font-weight: bold;
            color: #1e3a5f;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
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
            <div class="text-center" style="text-align:center;">
                <h2 style="color:#1e3a5f;margin-bottom:10px;">Welcome to {{ $org['name'] }}</h2>
                <p style="color:#666;">Date: {{ now()->format('F j, Y') }}</p>
            </div>
            
            <div class="member-info">
                <table>
                    <tr>
                        <td class="label">Member Name:</td>
                        <td>{{ $member->first_name }} {{ $member->last_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Member No:</td>
                        <td>{{ $member->member_no }}</td>
                    </tr>
                    <tr>
                        <td class="label">Membership Type:</td>
                        <td>{{ $member->membershipType?->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Membership Start Date:</td>
                        <td>{{ $member->membership_start_date?->format('F j, Y') }}</td>
                    </tr>
                    @if($member->email)
                    <tr>
                        <td class="label">Email:</td>
                        <td>{{ $member->email }}</td>
                    </tr>
                    @endif
                    @if($member->phone)
                    <tr>
                        <td class="label">Phone:</td>
                        <td>{{ $member->phone }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            
            <div class="welcome-text">
                <p>Dear <strong>{{ $member->first_name }} {{ $member->last_name }}</strong>,</p>
                
                <p><strong>Welcome to {{ $org['name'] }}!</strong></p>
                
                <p>{{ $org['welcome_message'] }}</p>
                
                <p>As a valued member, you now have access to all our member benefits, events, and activities. We look forward to your active participation in our community.</p>
                
                <p>Please feel free to reach out to us if you have any questions or need any assistance.</p>
                
                <p>Thank you for joining us!</p>
            </div>
            
            <div class="signature">
                <div class="signature-line">
                    <div class="signature-name">{{ $org['founder_name'] }}</div>
                    <div>{{ $org['founder_title'] }}</div>
                    <div>{{ $org['name'] }}</div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p>{{ $org['name'] }} | {{ $org['address'] }} | {{ $org['phone'] }} | {{ $org['email'] }}</p>
            <p>{{ $org['website'] }}</p>
        </div>
    </div>
</body>
</html>
