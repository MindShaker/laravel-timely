<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absence extends Model
{
    protected $fillable = ['user_id', 'date', 'type', 'remote'];

    protected $casts = [
        'date'   => 'date',
        'remote' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
