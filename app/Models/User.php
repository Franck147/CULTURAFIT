<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'rol',
        'estado',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    public function esCajero(): bool
    {
        return $this->rol === 'cajero';
    }

    public function estaActivo(): bool
    {
        return $this->estado === 'activo';
    }

    public function cajas()
    {
        return $this->hasMany(Caja::class, 'usuario_id');
    }

    public function gastos()
    {
        return $this->hasMany(Gasto::class, 'usuario_id');
    }
}
