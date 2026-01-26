<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AvailabilitySlot extends Model
{
    use HasFactory;

    protected $fillable = [
        'veterinarian_id',
        'service_type',
        'starts_at',
        'ends_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function veterinarian()
    {
        return $this->belongsTo(Veterinarian::class);
    }
}
