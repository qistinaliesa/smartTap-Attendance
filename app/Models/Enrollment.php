<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'course_id',
        'enrolled_at'
    ];

    protected $casts = [
        'enrolled_at' => 'datetime'
    ];

    /**
     * Get the student (card) associated with this enrollment.
     */
    public function card()
    {
        return $this->belongsTo(Card::class, 'student_id', 'id');
    }

    /**
     * Get the course associated with this enrollment.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
