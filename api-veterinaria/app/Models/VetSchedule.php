<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VetSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'veterinarian_id',
        'weekday',
        'start_time',
        'end_time',
        'slot_minutes',
        'active',
    ];

    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class);
    }
}
