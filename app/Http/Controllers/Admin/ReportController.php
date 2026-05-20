<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Payment;
use App\Models\MembershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MembersExport;

class ReportController extends Controller
{
    public static function formatPhoneNumber($phone)
    {
        if (!$phone) return '';
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($digits) === 10) {
            return '(' . substr($digits, 0, 3) . ') ' . substr($digits, 3, 3) . '-' . substr($digits, 6);
        }
        return $phone;
    }

    public function memberList(Request $request)
    {
        $status = $request->get('status');
        $membershipType = $request->get('membership_type_id');
        $year = $request->get('year');
        $searchColumn = $request->get('search_column', 'all');
        $searchValue = $request->get('search_value', '');

        $query = Member::with('membershipType');

        // Column-specific search
        if ($searchValue) {
            if ($searchColumn === 'all') {
                $query->where(function($q) use ($searchValue) {
                    $q->where('first_name', 'like', "%{$searchValue}%")
                      ->orWhere('last_name', 'like', "%{$searchValue}%")
                      ->orWhere('member_no', 'like', "%{$searchValue}%")
                      ->orWhere('phone', 'like', "%{$searchValue}%")
                      ->orWhere('city', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('receipt_no', 'like', "%{$searchValue}%");
                });
            } else {
                $query->where($searchColumn, 'like', "%{$searchValue}%");
            }
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($membershipType) {
            $query->where('membership_type_id', $membershipType);
        }

        if ($year) {
            $query->where('joining_year', $year);
        }

        $members = $query->orderBy('last_name')->orderBy('first_name')->get();
        $membershipTypes = MembershipType::active()->orderBy('name')->get();
        $years = Member::distinct()->pluck('joining_year')->filter()->sort()->reverse();

        return view('admin.reports.member-list', compact('members', 'membershipTypes', 'years'));
    }

    public function activeInactive(Request $request)
    {
        $type = $request->get('type', 'active');
        
        $query = Member::with('membershipType');
        
        if ($type === 'active') {
            $query->active();
        } else {
            $query->inactive();
        }

        $members = $query->orderBy('last_name')->orderBy('first_name')->get();

        return view('admin.reports.active-inactive', compact('members', 'type'));
    }

    public function duesSummary(Request $request)
    {
        $year = $request->get('year', date('Y'));

        $membershipTypes = MembershipType::withCount(['members' => function ($query) use ($year) {
            $query->where('joining_year', $year);
        }])->get();

        $paymentsByType = Payment::whereYear('payment_date', $year)
            ->join('members', 'payments.member_id', '=', 'members.id')
            ->join('membership_types', 'members.membership_type_id', '=', 'membership_types.id')
            ->select(
                'membership_types.name',
                DB::raw('SUM(payments.amount) as total_collected'),
                DB::raw('COUNT(payments.id) as payment_count')
            )
            ->groupBy('membership_types.id', 'membership_types.name')
            ->get();

        $totalExpected = Member::where('joining_year', $year)->count() * MembershipType::avg('fee_amount');
        $totalCollected = Payment::whereYear('payment_date', $year)->sum('amount');

        return view('admin.reports.dues-summary', compact('membershipTypes', 'paymentsByType', 'year', 'totalExpected', 'totalCollected'));
    }

    public function exportMembers(Request $request)
    {
        $format = $request->get('format', 'csv');
        $status = $request->get('status');
        $membershipType = $request->get('membership_type_id');
        $year = $request->get('year');
        $searchColumn = $request->get('search_column', 'all');
        $searchValue = $request->get('search_value', '');

        $query = Member::with('membershipType');

        // Column-specific search
        if ($searchValue) {
            if ($searchColumn === 'all') {
                $query->where(function($q) use ($searchValue) {
                    $q->where('first_name', 'like', "%{$searchValue}%")
                      ->orWhere('last_name', 'like', "%{$searchValue}%")
                      ->orWhere('member_no', 'like', "%{$searchValue}%")
                      ->orWhere('phone', 'like', "%{$searchValue}%")
                      ->orWhere('city', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('receipt_no', 'like', "%{$searchValue}%");
                });
            } else {
                $query->where($searchColumn, 'like', "%{$searchValue}%");
            }
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($membershipType) {
            $query->where('membership_type_id', $membershipType);
        }

        if ($year) {
            $query->where('joining_year', $year);
        }

        $members = $query->orderBy('last_name')->orderBy('first_name')->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.reports.exports.members-pdf', compact('members'));
            return $pdf->download('members_' . date('Y-m-d') . '.pdf');
        }

        return Excel::download(new MembersExport($members), 'members_' . date('Y-m-d') . '.' . $format);
    }
}
