<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MenuItem extends Model
{
    use SoftDeletes;
    protected $fillable = ['menu_id', 'name', 'category', 'sort_order'];
    public function menu() { return $this->belongsTo(Menu::class); }
}
