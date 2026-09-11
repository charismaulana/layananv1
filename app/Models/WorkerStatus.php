<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkerStatus extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'slug', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function users() { return $this->hasMany(User::class); }
}
