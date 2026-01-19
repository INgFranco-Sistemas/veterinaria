<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Veterinarian extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_type',
        'document_number',
        'full_name',
        'email',
        'phone',
        'specialty',
        'attention_area',
        'active'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
