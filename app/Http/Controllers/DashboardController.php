<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Http\Resources\TaskResource;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $totalPendingTasks = Task::query()->where('status', 'pending')->count();
        $myPendingTasks = Task::query()->where('status', 'pending')->where('assigned_user_id', auth()->user()->id)->count();

        $totalInprogressTasks = Task::query()->where('status', 'in_progress')->count();
        $myInprogressTasks = Task::query()->where('status', 'in_progress')->where('assigned_user_id', auth()->user()->id)->count();

        $totalCompletedTasks = Task::query()->where('status', 'completed')->count();
        $myCompletedTasks = Task::query()->where('status', 'completed')->where('assigned_user_id', auth()->user()->id)->count();

        $activeTasks = Task::query()
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('assigned_user_id', $user->id)
            ->limit(10)
            ->get();
        // dd($activeTasks->toArray());
        $activeTasks = TaskResource::collection($activeTasks);

        return inertia('Dashboard', compact('totalPendingTasks', 'myPendingTasks', 'totalInprogressTasks', 'myInprogressTasks', 'totalCompletedTasks', 'myCompletedTasks', 'activeTasks'));
    }
}
