<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Honor extends Model
{
    use HasFactory;

    protected $table = 'honor';

    protected $fillable = [
        'url_name',
        'url',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];
    public function setDateAttribute($value): void
    {
        try {
            $this->attributes['date'] = Carbon::createFromFormat('Y/m/d H:i', $value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Log::warning('Invalid date format for honor: ' . $value);
            $this->attributes['date'] = null; // Or handle as needed
        }
    }

    public function getDateAttribute($value): string
    {
        try {
            return $value ? Carbon::parse($value)->format('Y/m/d H:i') : 'N/A';
        } catch (\Exception $e) {
            Log::warning('Failed to parse date for honor ID ' . $this->id . ': ' . $value);
            return 'N/A';
        }
    }
}
