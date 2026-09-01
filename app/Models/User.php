<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'tipo',
        'inicio_almoco',
        'hora_entrada',
        'hora_saida',
        'birthdate',
        'calendar_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'birthdate'         => 'date',
        ];
    }

    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    public function isAdmin(): bool
    {
        return $this->tipo === 'admin';
    }

    public function getOrCreateCalendarToken(): string
    {
        if (!$this->calendar_token) {
            $this->calendar_token = Str::random(48);
            $this->saveQuietly();
        }
        return $this->calendar_token;
    }
}
