<?php

namespace App\Http\Controllers\Admin\Events;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Http\Request;

class EventDashboardController extends Controller
{
    public function index()
    {
        $now = now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Past events
        $pastEvents = Event::where('end_date', '<', $now->toDateString())
            ->orderBy('start_date', 'desc')
            ->limit(5)
            ->get();

        // Current month events
        $currentMonthEvents = Event::whereMonth('start_date', $currentMonth)
            ->whereYear('start_date', $currentYear)
            ->orWhere(function($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('end_date', $currentMonth)
                  ->whereYear('end_date', $currentYear);
            })
            ->orderBy('start_date')
            ->get();

        // Future events
        $futureEvents = Event::where('start_date', '>', $now->toDateString())
            ->orderBy('start_date')
            ->limit(5)
            ->get();

        // Stats
        $totalEvents = Event::count();
        $activeEvents = Event::where('status', 'ACTIVE')->where('start_date', '>=', $now->toDateString())->count();
        $totalAttendees = EventAttendee::count();
        $totalRevenue = EventAttendee::where('status', 'CONFIRMED')->sum('amount_paid');

        // This month stats
        $thisMonthConfirmed = EventAttendee::whereHas('event', function($q) use ($currentMonth, $currentYear) {
            $q->whereMonth('start_date', $currentMonth)->whereYear('start_date', $currentYear);
        })->where('status', 'CONFIRMED')->count();

        $thisMonthTentative = EventAttendee::whereHas('event', function($q) use ($currentMonth, $currentYear) {
            $q->whereMonth('start_date', $currentMonth)->whereYear('start_date', $currentYear);
        })->where('status', 'TENTATIVE')->count();

        return view('admin.events.dashboard', compact(
            'pastEvents',
            'currentMonthEvents',
            'futureEvents',
            'totalEvents',
            'activeEvents',
            'totalAttendees',
            'totalRevenue',
            'thisMonthConfirmed',
            'thisMonthTentative'
        ));
    }
}
