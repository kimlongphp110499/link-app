<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'url', 'total_votes', 'clan_id'];

    public function voteHistories()
    {
        return $this->hasMany(VoteHistory::class);
    }

    public function getTotalVotesAttribute()
    {
        return $this->voteHistories()->sum('points_voted');
    }

    public function clan()
    {
        return $this->belongsTo(Clan::class);
    }
}
