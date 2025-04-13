<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClanTempMember extends Model
{
    use HasFactory;

    protected $table = 'honor';

    protected $fillable = ['user_id', 'link_id', 'clan_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clan()
    {
        return $this->belongsTo(Clan::class);
    }
}
