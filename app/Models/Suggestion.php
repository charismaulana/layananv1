<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Suggestion extends Model
{
    use SoftDeletes;
    protected $fillable = ['user_id', 'region_id', 'category', 'content', 'is_read'];
    protected $casts = ['is_read' => 'boolean'];

    public function user()   { return $this->belongsTo(User::class); }
    public function region() { return $this->belongsTo(Region::class); }
}
