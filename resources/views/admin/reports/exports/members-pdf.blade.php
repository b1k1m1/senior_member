<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Members Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #1e3a5f; padding-bottom: 10px; }
        .company-name { font-size: 18px; font-weight: bold; color: #1e3a5f; }
        .company-address { font-size: 10px; color: #666; }
        .report-title { font-size: 14px; margin-top: 8px; }
        .report-date { font-size: 10px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 8px; }
        th, td { border: 1px solid #ddd; padding: 3px; text-align: left; }
        th { background-color: #1e3a5f; color: white; font-size: 8px; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #666; }
        .page-number { float: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Your Organization Name</div>
        <div class="company-address">123 Main Street, City, State 12345 | Phone: (555) 123-4567</div>
        <div class="report-title">Members Report</div>
        <div class="report-date">Generated on {{ now()->format('F j, Y g:i A') }}</div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Member No</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Spouse First</th>
                <th>Spouse Last</th>
                <th>DOB</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Cell</th>
                <th>Address 1</th>
                <th>Address 2</th>
                <th>City</th>
                <th>State</th>
                <th>Zip</th>
                <th>County</th>
                <th>Year</th>
                <th>Status</th>
                <th>Status Reason</th>
                <th>Receipt No</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($members as $member)
            <tr>
                <td>{{ $member->member_no }}</td>
                <td>{{ $member->first_name }}</td>
                <td>{{ $member->last_name }}</td>
                <td>{{ $member->spouse_first_name }}</td>
                <td>{{ $member->spouse_last_name }}</td>
                <td>{{ $member->dateofbirth ? \Carbon\Carbon::parse($member->dateofbirth)->format('m/d/Y') : '' }}</td>
                <td>{{ $member->email }}</td>
                <td>{{ \App\Http\Controllers\Admin\ReportController::formatPhoneNumber($member->phone) }}</td>
                <td>{{ \App\Http\Controllers\Admin\ReportController::formatPhoneNumber($member->cell_phone) }}</td>
                <td>{{ $member->address1 }}</td>
                <td>{{ $member->address2 }}</td>
                <td>{{ $member->city }}</td>
                <td>{{ $member->state }}</td>
                <td>{{ $member->zip }}</td>
                <td>{{ $member->county }}</td>
                <td>{{ $member->joining_year }}</td>
                <td>{{ $member->status }}</td>
                <td>{{ $member->status_reason }}</td>
                <td>{{ $member->receipt_no }}</td>
                <td>{{ $member->amount }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <span class="page-number">Page {{ $page ?? 1 }}</span>
    </div>
</body>
</html>
