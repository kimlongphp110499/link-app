<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClanPointHistory extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'clan_id'];

    // Quan hệ với bảng User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ với bảng Clan
    public function clan()
    {
        return $this->belongsTo(Clan::class);
    }
}
