<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'card_id',
        'date',
        'time_in',
        'time_out',
    ];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
