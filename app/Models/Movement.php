<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Movement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'requester_id', 'movement_date', 'from_region_id', 'to_region_id',
        'reason', 'status', 'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'movement_date' => 'date',
        'approved_at'   => 'datetime',
    ];

    public function requester()   { return $this->belongsTo(User::class, 'requester_id'); }
    public function fromRegion()  { return $this->belongsTo(Region::class, 'from_region_id'); }
    public function toRegion()    { return $this->belongsTo(Region::class, 'to_region_id'); }
    public function approver()    { return $this->belongsTo(User::class, 'approved_by'); }
    public function people()      { return $this->hasMany(MovementPerson::class); }

    public function isPending()  { return $this->status === 'pending'; }
    public function isApproved() { return $this->status === 'approved'; }

    public function scopePending($q) { return $q->where('status', 'pending'); }
}

class MovementPerson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['movement_id', 'user_id', 'meal_type_ids'];
    protected $casts = ['meal_type_ids' => 'array'];

    public function movement() { return $this->belongsTo(Movement::class); }
    public function user()     { return $this->belongsTo(User::class); }
}
