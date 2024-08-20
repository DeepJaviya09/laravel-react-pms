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
use Illuminate\Support\Facades\Http;

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
        $data = $request->validated();
        $image = $data['image'] ?? null;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();

        if ($image) {
            // Generate a unique file name
            $fileName = Str::random(40) . '.' . $image->getClientOriginalExtension();

            // Get the file content as binary
            $fileContent = file_get_contents($image->getPathname());

            // Make an HTTP PUT request to upload the image to Vercel Blob
            $response = Http::withToken(env('BLOB_READ_WRITE_TOKEN'))
                ->withoutVerifying()
                ->put('https://api.vercel.com/v1/blob', [
                    'name' => $fileName,
                    'content' => base64_encode($fileContent),
                    'contentType' => $image->getMimeType(),
                ]);

            if ($response->successful()) {
                // Store the image URL in the database
                $data['image_path'] = $response->json('url');
            } else {
                // Log the response for debugging
                \Log::error('Vercel Blob Upload Failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return back()->withErrors('Failed to upload image to Vercel Blob. Status: ' . $response->status());
            }
        }

        // Create the project with the data
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
        $data = $request->validated();
        $image = $data['image'] ?? null;
        $data['updated_by'] = Auth::id();
        
        // Check if a new image is provided
        if ($image) {
            // Delete the existing image from Vercel Blob if it exists
            if ($project->image_path) {
                $this->deleteBlob($project->image_path);
            }
    
            // Generate a unique file name
            $fileName = Str::random(40) . '.' . $image->getClientOriginalExtension();
    
            // Get the file content as binary
            $fileContent = file_get_contents($image->getPathname());
    
            // Make an HTTP PUT request to upload the image to Vercel Blob
            $response = Http::withToken(env('BLOB_READ_WRITE_TOKEN'))
                ->withoutVerifying()
                ->put('https://api.vercel.com/v1/blob', [
                    'name' => $fileName,
                    'content' => base64_encode($fileContent),
                    'contentType' => $image->getMimeType(),
                ]);
    
            if ($response->successful()) {
                // Store the new image URL in the database
                $data['image_path'] = $response->json('url');
            } else {
                // Log the response for debugging
                \Log::error('Vercel Blob Upload Failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return back()->withErrors('Failed to upload image to Vercel Blob. Status: ' . $response->status());
            }
        }
    
        // Update the project with the new data
        $project->update($data);
    
        return to_route('project.index')->with('success', "Project {$project->name} has been updated!");
    }


    /**
     * Delete the image from Vercel Blob.
     *
     * @param string $imageUrl
     * @return void
     */
    private function deleteBlob(string $blobUrl): void
    {
        // Assuming the URL is the full URL; adjust if you need only a part
        $response = Http::withToken(env('BLOB_READ_WRITE_TOKEN'))
            ->withoutVerifying()
            ->delete($blobUrl);  // Directly use the URL if that's how Vercel expects it
    
        \Log::info("Delete Blob Response Status: " . $response->status());
        \Log::info("Delete Blob Response Body: " . $response->body());
    
        if (!$response->successful()) {
            \Log::error('Vercel Blob Delete Failed', [
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
        }
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
