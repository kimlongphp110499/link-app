<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClanLink extends Model
{
    use HasFactory;

    protected $table = 'clan_link';
    protected $fillable = ['clan_id', 'link_id'];

    public function clan()
    {
        return $this->belongsTo(Clan::class);
    }

    public function link()
    {
        return $this->belongsTo(Link::class);
    }
}