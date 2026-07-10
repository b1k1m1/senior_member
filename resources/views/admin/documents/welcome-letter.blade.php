<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>

body{
    font-family: DejaVu Sans, sans-serif;
    font-size:12px;
    color:#000;
}

/* HEADER */

.header{
    border-bottom:2px solid #000;
    padding-bottom:10px;
}

.org-name{
    font-size:20px;
    font-weight:bold;
}

.org-info{
    font-size:11px;
}

/* SIDEBAR */

.sidebar-title{
    font-weight:bold;
    margin-bottom:10px;
}

/* RECEIPT */

.receipt{
    border:1px solid #000;
    margin-top:15px;
}

.receipt th{
    background:#eee;
}

.footer{
    border-top:1px solid #000;
    margin-top:20px;
    font-size:10px;
    text-align:center;
}

</style>

</head>

<body>

<!-- HEADER -->

<table width="100%" class="header">

<tr>

<td width="20%" align="center">

@if($org['founder_photo_base64'])
<img src="{{ $org['founder_photo_base64'] }}" width="80">
@endif

<br>

<b>{{ $org['founder_name'] }}</b>

</td>


<td width="60%" align="center">

<div class="org-name">
{{ $org['name'] }}
</div>

<div class="org-info">

{{ $org['address'] }} <br>

Phone : {{ $org['phone'] }} |
Email : {{ $org['email'] }} <br>

Tax ID : {{ $org['tax_id'] }}

</div>

</td>


<td width="20%" align="right">

@if($org['logo_base64'])
<img src="{{ $org['logo_base64'] }}" width="80">
@endif

</td>

</tr>

</table>


<!-- BODY -->

<table width="100%" style="margin-top:25px">

<tr>

<!-- LEFT SIDEBAR -->

<td width="30%" valign="top">

<div class="sidebar-title">

Current Office Bearers

</div>
<br><br>

    @foreach($officeBearers as $office)
        <strong>{{ $office->position }}</strong><br>
        {{ $office->name }}<br>
        {{ $office->phone }}
        <br><br>
    @endforeach
</td>

<!-- RIGHT CONTENT -->

<td width="70%" valign="top">

Date : {{ $today }}

<br><br>
Dear
@foreach($receiptMembers as $receiptMember)
    {{ $receiptMember->first_name }} {{ $receiptMember->last_name }}@if(!$loop->last) and @endif
@endforeach,

<br><br>

We are pleased to welcome you as a member of <i><b>{{ $org['name'] }}</b></i>.

<br><br>

<i>IASONJ</i> is trying to go paperless therefore we have stopped ID Card.<br><br>

In any future <i>IASONJ</i> event or tour, you will only require the membership ID.<br><br>

Again, we welcome you as a members of <i>IASONJ</i> family.<br><br>

By joining <i>IASONJ</i> you have become part of a large of Indian American Senior Organization. <br><br>

Our mission is two-fold. The first is to be a resource for you by providing you with
information regarding topics including health, immigration, citizenship and social welfare. <br><br>

The Second is to be social outlet by providing community activities, cultural programs,
temple visits and trips both domestic and international.<br><br>

We encourage you to help us to make our senior community a better place. Your comments, suggestions and
support are always welcome.<br><br>

Once again, welcome to <i>IASONJ</i>. We look forward to seeing you at our events.

<br><br>


<!-- RECEIPT -->

<table width="100%" class="receipt" cellpadding="6">

<tr>

<th colspan="2" align="center">

INDO-AMERICAN SENIORS ORGANIZATION OF NEW JERSEY

</th>

</tr>

<tr>
    <td width="40%">Member Name</td>
    <td>

        <table width="100%" cellpadding="0" cellspacing="0" style="border:0;">
            @foreach($receiptMembers as $receiptMember)
                <tr>
                    <td style="border:0; width:65%; padding:2px 0;">
                        {{ $receiptMember->first_name }} {{ $receiptMember->last_name }}
                    </td>
                    <td style="border:0; width:35%; padding:2px 0;">
                        {{ $receiptMember->member_no }}
                    </td>
                </tr>
            @endforeach
        </table>
    </td>

<tr>
    <td>Receipt Number</td>
    <td>{{ $member->receipt_no }}</td>
</tr>

<tr>
    <td>Receipt Date</td>
    <td>{{ \Carbon\Carbon::parse($member->membership_start_date)->format('m/d/Y') }}</td>
</tr>

<tr>
    <td>Amount</td>
    <td>{{ number_format($receiptMembers->sum('amount'), 2) }}</td>
</tr>

<tr>
    <td>Membership Type</td>
    <td>{{ $member->membershipType?->name }}</td>
</tr>

</table>

<br><br>

Sincerely yours,

<br><br>

@if($presidentSignatureExists && $presidentSignaturePath)
    <img
        src="{{ $presidentSignaturePath }}"
        alt="President Signature"
        style="width: 150px; height: auto; display: block;"
    >
@else
    <br><br>
@endif

<div style="margin-top: 5px;">
{{ $president->name }}

<br>

(President)

</td>

</tr>

</table>

<!--
<div class="footer">
{{--
{{ $org['name'] }} |
{{ $org['address'] }} |
{{ $org['phone'] }}

--}}
</div>
-->

</body>
</html>
