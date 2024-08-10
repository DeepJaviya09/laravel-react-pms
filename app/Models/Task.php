<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Model as Eloquent;

class Task extends Eloquent
{
    use HasFactory;

    protected $connection = 'mongodb';

    protected $fillable = ['image_path', 'name', 'description', 'status', 'priority', 'due_date', 'created_by', 'updated_by', 'assigned_user_id', 'project_id'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
