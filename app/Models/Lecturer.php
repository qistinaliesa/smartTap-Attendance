<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Lecturer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'staff_id',
        'kulliyyah',
        'department',
        'password',
    ];

    // If you don't want the password to be visible in JSON responses
    protected $hidden = [
        'password',
        'remember_token',
    ];
}
