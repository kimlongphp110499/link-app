<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'url', 'total_votes', 'clan_id', 'video_id', 'duration'];

    public function voteHistories()
    {
        return $this->hasMany(VoteHistory::class);
    }

    public function getTotalVotesAttribute()
    {
        return $this->voteHistories()->sum('points_voted');
    }

    public function clans()
    {
        return $this->belongsToMany(Clan::class, 'clan_link', 'link_id', 'clan_id');
    }
}
