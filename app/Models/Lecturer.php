<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Lecturer extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'staff_id', 'kulliyyah', 'department', 'password'];
}
