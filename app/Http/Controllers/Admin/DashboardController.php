<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Payment;
use App\Models\MembershipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_members' => Member::count(),
            'active_members' => Member::active()->count(),
            'inactive_members' => Member::inactive()->count(),
            'total_payments' => Payment::sum('amount'),
            'total_revenue_this_year' => Payment::whereYear('payment_date', date('Y'))->sum('amount'),
        ];

        $recentMembers = Member::latest()->take(5)->get();
        
        $recentPayments = Payment::with('member')
            ->latest()
            ->take(5)
            ->get();

        $membershipStats = Member::with('membershipType')
            ->select('members.membership_type_id', DB::raw('count(*) as count'))
            ->groupBy('members.membership_type_id')
            ->get();

        $monthlyPayments = Payment::select(
            DB::raw('MONTH(payment_date) as month'),
            DB::raw('SUM(amount) as total')
        )
        ->whereYear('payment_date', date('Y'))
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return view('admin.dashboard', compact('stats', 'recentMembers', 'recentPayments', 'membershipStats', 'monthlyPayments'));
    }
}
