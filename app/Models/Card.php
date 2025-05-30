<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Card extends Model
{
   use HasFactory;

    protected $fillable = [
        'uid',
        'name',
        'matric_id',
    ];

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
}
