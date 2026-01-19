<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pet extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'name',
        'species',
        'breed',
        'sex',
        'birth_date',
        'weight_kg',
        'notes',
        'active'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
