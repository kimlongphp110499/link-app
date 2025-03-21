<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clan extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'points'];

    // Quan hệ với bảng ClanPointHistory
    public function pointHistories()
    {
        return $this->hasMany(ClanPointHistory::class);
    }

    public function link()
    {
        return $this->hasOne(Link::class);
    }
}