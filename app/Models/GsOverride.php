<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GsOverride extends Model
{
    protected $fillable = [
        'gs_user_id', 'overridable_type', 'overridable_id',
        'reason', 'old_values', 'new_values', 'overridden_at',
    ];

    protected $casts = [
        'old_values'    => 'array',
        'new_values'    => 'array',
        'overridden_at' => 'datetime',
    ];

    public function gsUser()    { return $this->belongsTo(User::class, 'gs_user_id'); }
    public function overridable() { return $this->morphTo(); }
}
