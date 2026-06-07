<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StaffLog;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Staff;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = StaffLog::query()->with(['causer', 'user', 'staff.restaurant']);

        // Search logic
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', '%'.$search.'%')
                  ->orWhere('causer_name', 'like', '%'.$search.'%')
                  ->orWhereHas('causer', function ($uq) use ($search) {
                      $uq->where('email', 'like', '%'.$search.'%');
                  })
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('email', 'like', '%'.$search.'%');
                  });
            });
        }

        // Action filter logic
        if ($actionType = $request->input('action_type')) {
            $query->where('action', $actionType);
        }

        // Date range filter logic
        if ($startDate = $request->input('start_date')) {
            $query->where('created_at', '>=', \Carbon\Carbon::parse($startDate)->startOfDay());
        }
        if ($endDate = $request->input('end_date')) {
            $query->where('created_at', '<=', \Carbon\Carbon::parse($endDate)->endOfDay());
        }

        // Restaurant filter logic
        if ($restaurantId = $request->input('restaurant_id')) {
            // Get all user IDs associated with this restaurant
            $staffUserIds = Staff::where('restaurant_id', $restaurantId)->pluck('user_id')->toArray();
            
            $pivotEmails = DB::connection('mongodb')
                ->table('restaurant_user')
                ->where('restaurant_id', $restaurantId)
                ->pluck('email')
                ->toArray();
            
            $pivotUserIds = User::whereIn('email', $pivotEmails)->pluck('id')->toArray();
            
            $userIds = array_unique(array_filter(array_merge($staffUserIds, $pivotUserIds)));
            
            $query->where(function ($q) use ($restaurantId, $userIds) {
                $q->whereHas('staff', function ($sq) use ($restaurantId) {
                    $sq->where('restaurant_id', $restaurantId);
                });
                if (!empty($userIds)) {
                    $q->orWhereIn('causer_id', $userIds)
                      ->orWhereIn('user_id', $userIds);
                }
            });
        }

        $logs = $query->latest()->paginate(15)->appends($request->all());

        // Transform results
        $logs->getCollection()->transform(function ($log) {
            // UAE Time conversion (Asia/Dubai is UTC+4)
            $log->uae_datetime = $log->created_at 
                ? $log->created_at->timezone('Asia/Dubai')->format('Y-m-d h:i A') 
                : '-';

            // Email resolution
            $log->user_email = $log->causer 
                ? $log->causer->email 
                : ($log->user ? $log->user->email : ($log->causer_name ?: '-'));

            // Target Email resolution (e.g. if User 1 updates details of User 2)
            $log->target_email = $log->user && $log->user_id !== $log->causer_id
                ? $log->user->email
                : null;

            // Restaurant name resolution
            $restaurantNames = [];
            if ($log->staff && $log->staff->restaurant) {
                $restaurantNames[] = $log->staff->restaurant->name;
            }
            if ($log->causer) {
                $pivotRows = DB::connection('mongodb')
                    ->table('restaurant_user')
                    ->where('email', $log->causer->email)
                    ->get();
                $restaurantIds = collect($pivotRows)->pluck('restaurant_id')->toArray();
                if (!empty($restaurantIds)) {
                    $names = Restaurant::whereIn('id', $restaurantIds)->pluck('name')->toArray();
                    $restaurantNames = array_merge($restaurantNames, $names);
                }
            }
            $log->restaurant_name = !empty($restaurantNames) 
                ? implode(', ', array_unique($restaurantNames)) 
                : '-';

            return $log;
        });

        // Fetch all restaurants for filter dropdown
        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);

        // Fetch all unique actions for filtering
        $actionTypes = array_filter(StaffLog::groupBy('action')->pluck('action')->toArray());

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,
            'restaurants' => $restaurants,
            'actionTypes' => array_values($actionTypes),
            'filters' => $request->only(['search', 'restaurant_id', 'action_type', 'start_date', 'end_date']),
        ]);
    }
}
