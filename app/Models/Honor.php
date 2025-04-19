<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Honor extends Model
{
    use HasFactory;

    protected $table = 'honors';

    protected $fillable = [
        'url_name',
        'url',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];
}
