<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = ['link_id', 'start_time'];
    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}
