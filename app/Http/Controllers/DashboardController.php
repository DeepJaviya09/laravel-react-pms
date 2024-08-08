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
        $totalPendingTasks = Task::query()->where('status', 'pending')->whereHas('project')->count();
        $myPendingTasks = Task::query()
                            ->where('status', 'pending')
                            ->whereHas('project')
                            ->where('assigned_user_id', auth()->user()->id)->
                            count();

        $totalInprogressTasks = Task::query()->where('status', 'in_progress')->whereHas('project')->count();
        $myInprogressTasks = Task::query()->where('status', 'in_progress')->whereHas('project')->where('assigned_user_id', auth()->user()->id)->count();

        $totalCompletedTasks = Task::query()->where('status', 'completed')->whereHas('project')->count();
        $myCompletedTasks = Task::query()->where('status', 'completed')->whereHas('project')->where('assigned_user_id', auth()->user()->id)->count();

        $activeTasks = Task::query()
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('assigned_user_id', $user->id)
            ->whereHas('project')
            ->limit(10)
            ->get();
            // dd($activeTasks->toArray());
            $activeTasks = TaskResource::collection($activeTasks);
            
        return inertia('Dashboard', compact('totalPendingTasks', 'myPendingTasks', 'totalInprogressTasks', 'myInprogressTasks', 'totalCompletedTasks', 'myCompletedTasks', 'activeTasks'));
    }
}
