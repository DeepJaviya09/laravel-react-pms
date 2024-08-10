<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Storage;

class ProjectResource extends JsonResource
{
    public static $wrap = false;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->formatDate($this->created_at),
            'due_date' => $this->formatDate($this->due_date),
            'status' => $this->status,
            'image_path' => $this->image_path ? Storage::url($this->image_path) : '',
            'createdBy' => $this->createdBy ? new UserResource($this->createdBy) : null,
            'updatedBy' => $this->updatedBy ? new UserResource($this->updatedBy) : null,
        ];
    }

    /**
     * Format date fields if they are valid.
     *
     * @param mixed $date
     * @return string|null
     */
    private function formatDate($date): ?string
    {
        if (is_array($date) && isset($date['$date'])) {
            return (new Carbon($date['$date']))->format('Y-m-d');
        }

        if ($date instanceof \DateTimeInterface) {
            return $date->format('Y-m-d');
        }

        if (is_string($date)) {
            return (new Carbon($date))->format('Y-m-d');
        }

        return null;
    }

}
