<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WasteRequest;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $now = Carbon::now();

        if($user->hasRole('Super Admin')){
            // All waste requests stats
            $totalRequests = WasteRequest::count();
            $statusCounts = WasteRequest::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $monthlyData = WasteRequest::selectRaw("DATE_FORMAT(created_at,'%b %Y') as month, COUNT(*) as total")
                ->groupBy('month')
                ->orderByRaw('MIN(created_at)')
                ->pluck('total', 'month')
                ->toArray();

            $typeDistribution = WasteRequest::selectRaw('waste_type, COUNT(*) as total')
                ->groupBy('waste_type')
                ->pluck('total', 'waste_type')
                ->toArray();

            $collectorsCount = User::role('collector')->count();
            $totalUsers = User::count();

            return view('dashboard.superadmin', compact(
                'monthlyData', 'typeDistribution', 'statusCounts', 'totalRequests', 'collectorsCount', 'totalUsers'
            ));
        }else if($user->hasRole('Admin')){
            // City corporation admin
             $cityId = $user->city_corporation_id;
            $wardId = $user->ward_id;

            // Base query
            $adminQuery = WasteRequest::with(['user','ward','cityCorporation','assignedTo']);

            // Ward Admin → filter by ward only
            if ($wardId) {
                $adminQuery->where('ward_id', $wardId);
            }
            // City Corporation Admin → filter by city_corporation_id
            else {
                $adminQuery->where('city_corporation_id', $cityId);
            }

            // Recent requests (latest 5)
            $recentRequests = $adminQuery->latest()->take(5)->get();

            // Summary counts
            $summary = [
                'pending'     => (clone $adminQuery)->where('status','pending')->count(),
                'assigned'    => (clone $adminQuery)->where('status','assigned')->count(),
                'in_progress' => (clone $adminQuery)->where('status','in_progress')->count(),
                'completed'   => (clone $adminQuery)->where('status','completed')->count(),
                'cancelled'   => (clone $adminQuery)->where('status','cancelled')->count(),
                'total'       => (clone $adminQuery)->count(),
            ];

            // Monthly chart data
            $monthlyData = WasteRequest::selectRaw("DATE_FORMAT(request_date, '%b %Y') AS m, COUNT(*) as total")
                ->when($wardId, fn($q) => $q->where('ward_id',$wardId))
                ->when(!$wardId, fn($q)=> $q->where('city_corporation_id',$cityId))
                ->groupBy('m')
                ->orderByRaw("MIN(request_date)")
                ->pluck('total','m')
                ->toArray();

            // Collectors under this admin
            $collectors = User::role('collector')
                ->when($wardId, fn($q)=> $q->where('ward_id',$wardId))
                ->when(!$wardId, fn($q)=> $q->where('city_corporation_id',$cityId))
                ->get(['id','name']);

            return view('dashboard.admin', [
                'recentRequests' => $recentRequests,
                'summary'        => $summary,
                'monthlyData'    => $monthlyData,
                'collectors'     => $collectors,
            ]);
        }else if($user->hasRole('Collector')){
            $today = $now->toDateString();

            $todayTasks = WasteRequest::where('assigned_to', $user->id)
                ->whereDate('pickup_date', $today)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->orderBy('pickup_date')
                ->get();

            $upcomingTasks = WasteRequest::where('assigned_to', $user->id)
                ->whereDate('pickup_date', '>', $today)
                ->whereIn('status', ['assigned'])
                ->orderBy('pickup_date')
                ->get();

            $completedCount = WasteRequest::where('assigned_to', $user->id)
                ->where('status', 'completed')
                ->count();

            $inProgressCount = WasteRequest::where('assigned_to', $user->id)
                ->where('status', 'in_progress')
                ->count();

            return view('dashboard.collector', compact(
                'todayTasks', 'upcomingTasks', 'completedCount', 'inProgressCount'
            ));
        } else if($user->hasRole('Citizen')) {
            $recentRequests = WasteRequest::where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get();

            $summary = WasteRequest::where('user_id', $user->id)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total','status')
                ->toArray();

            $total = array_sum($summary);

            $monthlyData = WasteRequest::where('user_id', $user->id)
                ->selectRaw("DATE_FORMAT(created_at,'%b %Y') as month, COUNT(*) as total")
                ->groupBy('month')
                ->orderByRaw('MIN(created_at)')
                ->pluck('total', 'month')
                ->toArray();

            return view('dashboard.citizen', compact(
                'recentRequests', 'summary', 'total', 'monthlyData'
            ));
        }else{
            return view('dashboard.default');
        }
    }

    /**
     * Return in-progress task count for a collector
     */
    public function collectorInProgressCount($id)
    {
        $count = WasteRequest::where('assigned_to',$id)
            ->where('status','in_progress')
            ->count();

        $name = User::find($id)?->name ?? 'Collector';

        return response()->json(['count' => $count, 'name' => $name]);
    }

    /**
     * Return list of all collectors
     */
    public function collectorsList()
    {
        $collectors = User::where('role','collector')
            ->get(['id','name']);

        return response()->json($collectors);
    }
}
