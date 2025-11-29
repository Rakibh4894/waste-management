<?php

namespace App\Http\Controllers;

use App\Models\CityCorporation;
use App\Models\WasteRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $now  = Carbon::now();

        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN DASHBOARD
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('Super Admin')) {

            // Total summaries
            $totalRequests   = WasteRequest::count();
            $totalUsers      = User::count();
            $collectorsCount = User::role('Collector')->count();

            // Monthly chart
            $monthlyData = WasteRequest::selectRaw("DATE_FORMAT(created_at, '%b %Y') AS month, COUNT(*) as total")
                ->groupBy('month')
                ->orderByRaw("MIN(created_at)")
                ->pluck('total', 'month')
                ->toArray();

            // Waste type distribution
            $typeDistribution = WasteRequest::selectRaw("waste_type, COUNT(*) as total")
                ->groupBy('waste_type')
                ->pluck('total', 'waste_type')
                ->toArray();

            // City-wise totals
            $cities = CityCorporation::select('title')
                ->withCount('requests')
                ->get()
                ->map(fn ($c) => [
                    'title'  => $c->title,
                    'total' => $c->requests_count
                ])
                ->toArray();

            return view('dashboard.superadmin', compact(
                'totalRequests',
                'totalUsers',
                'collectorsCount',
                'monthlyData',
                'typeDistribution',
                'cities'
            ));
        }

        /*
        |--------------------------------------------------------------------------
        | CITY CORPORATION ADMIN / WARD ADMIN DASHBOARD
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('Admin')) {

            $cityId = $user->city_corporation_id;
            $wardId = $user->ward_id;

            // Base query
            $adminQuery = WasteRequest::with(['user', 'ward', 'cityCorporation', 'assignedTo']);

            // Ward Admin → filter by ward
            if ($wardId) {
                $adminQuery->where('ward_id', $wardId);
            }
            // City Admin → filter by city
            else {
                $adminQuery->where('city_corporation_id', $cityId);
            }

            // Latest requests
            $recentRequests = (clone $adminQuery)->latest()->take(5)->get();

            // Summary counts
            $summary = [
                'pending'     => (clone $adminQuery)->where('status', 'pending')->count(),
                'assigned'    => (clone $adminQuery)->where('status', 'assigned')->count(),
                'in_progress' => (clone $adminQuery)->where('status', 'in_progress')->count(),
                'completed'   => (clone $adminQuery)->where('status', 'completed')->count(),
                'cancelled'   => (clone $adminQuery)->where('status', 'cancelled')->count(),
                'total'       => (clone $adminQuery)->count(),
            ];

            // Monthly data
            $monthlyData = WasteRequest::selectRaw("DATE_FORMAT(request_date, '%b %Y') AS m, COUNT(*) as total")
                ->when($wardId, fn ($q) => $q->where('ward_id', $wardId))
                ->when(!$wardId, fn ($q) => $q->where('city_corporation_id', $cityId))
                ->groupBy('m')
                ->orderByRaw("MIN(request_date)")
                ->pluck('total', 'm')
                ->toArray();

            // Collectors under this city/ward
            $collectors = User::role('Collector')
                ->when($wardId, fn ($q) => $q->where('ward_id', $wardId))
                ->when(!$wardId, fn ($q) => $q->where('city_corporation_id', $cityId))
                ->get(['id', 'name']);

            return view('dashboard.admin', compact(
                'recentRequests',
                'summary',
                'monthlyData',
                'collectors'
            ));
        }

        /*
        |--------------------------------------------------------------------------
        | COLLECTOR DASHBOARD
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('Collector')) {

            $today = $now->toDateString();

            $todayTasks = WasteRequest::where('assigned_to', $user->id)
                ->whereDate('pickup_date', $today)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->orderBy('pickup_date')
                ->get();

            $upcomingTasks = WasteRequest::where('assigned_to', $user->id)
                ->whereDate('pickup_date', '>', $today)
                ->where('status', 'assigned')
                ->orderBy('pickup_date')
                ->get();

            $completedCount = WasteRequest::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->count();

            $inProgressCount = WasteRequest::where('assigned_to', $user->id)
                ->where('status', 'in_progress')
                ->count();

            return view('dashboard.collector', compact(
                'todayTasks',
                'upcomingTasks',
                'completedCount',
                'inProgressCount'
            ));
        }

        /*
        |--------------------------------------------------------------------------
        | CITIZEN DASHBOARD
        |--------------------------------------------------------------------------
        */
        if ($user->hasRole('Citizen')) {

            $recentRequests = WasteRequest::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            $summary = WasteRequest::where('user_id', $user->id)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $total = array_sum($summary);

            $monthlyData = WasteRequest::where('user_id', $user->id)
                ->selectRaw("DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as total")
                ->groupBy('month')
                ->orderByRaw("MIN(created_at)")
                ->pluck('total', 'month')
                ->toArray();

            return view('dashboard.citizen', compact(
                'recentRequests',
                'summary',
                'total',
                'monthlyData'
            ));
        }

        return view('dashboard.default');
    }

    /*
    |--------------------------------------------------------------------------
    | API: Collector In-progress Task Count
    |--------------------------------------------------------------------------
    */
    public function collectorInProgressCount($id)
    {
        $count = WasteRequest::where('assigned_to', $id)
            ->where('status', 'in_progress')
            ->count();

        $name = User::find($id)?->name ?? 'Collector';

        return response()->json(['count' => $count, 'name' => $name]);
    }

    /*
    |--------------------------------------------------------------------------
    | API: List of Collectors
    |--------------------------------------------------------------------------
    */
    public function collectorsList()
    {
        $collectors = User::role('Collector')->get(['id', 'name']);

        return response()->json($collectors);
    }
}
