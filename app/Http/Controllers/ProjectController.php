<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\TaskResource;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Project::query();
        // dd($query->get());
        $sortFields = request("sort_field", 'created_at');
        $sortDirection = request("sort_direction", "desc");

        if(request("name")) {
            $query->where("name","like","%". request("name")."%");
        }

        if(request("status")) {
            $query->where("status",request("status"));
        }

        $projects = $query->orderBy($sortFields, $sortDirection)
                        ->paginate(10)->onEachSide(1);
        // dd($projects);
        return inertia("Project/Index", [
            "projects" => ProjectResource::collection($projects),
            'queryParams' =>request()->query() ?: null,
            'success' => session('success'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia("Project/Create");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        // dd( Auth::id());
        $data = $request->validated();
        /** @var $image \Illuminate\Http\UploadedFile */
        $image = $data['image'] ?? null;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        // dd($data);
        if ($image) {
            $data['image_path'] = $image->store('project/'.Str::random(), 'public' );
        }
        Project::create($data);

        return to_route('project.index')->with('success', 'Project Created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $query = $project->tasks();
        $sortFields = request("sort_field", 'created_at');
        $sortDirection = request("sort_direction", "desc");

        if(request("name")) {
            $query->where("name","like","%". request("name")."%");
        }

        if(request("status")) {
            $query->where("status",request("status"));
        }

        $tasks = $query->orderBy($sortFields, $sortDirection)->paginate(10)->onEachSide(1);

        return inertia('Project/Show', [
            'project' => new ProjectResource($project),
            'tasks' => TaskResource::collection($tasks),
            'queryParams' =>request()->query() ?: null,
            'success' => session('success'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        return inertia('Project/Edit', [
            'project' => new ProjectResource($project),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        // dd($request->all());
        $data = $request->validated();
        // dd($data);
        $image = $data['image'] ?? null;
        $data['updated_by'] = Auth::id();
        
        if ($image) {
            if ($project->image_path) {
                Storage::disk('public')->delete(dirname($project->image_path));
            }
            $data['image_path'] = $image->store('project/'.Str::random(), 'public');
        }
        $project->update($data);

        return to_route('project.index')->with('success', "Project {$project->name} hasbeen updated !");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $name = $project->name;
        $project->delete();
        if ($project->image_path) {
            Storage::disk('public')->delete(dirname($project->image_path));
        }
        return to_route('project.index')->with('success', "Project {$name} deleted!");
    }
}
