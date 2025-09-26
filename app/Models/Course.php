<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_code',
        'title',
        'credit_hours',
        'section',
        'lecturer_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credit_hours' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the lecturer that owns the course.
     */
    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function todayAttendancePercentage()
    {
        $totalStudents = $this->enrollments()->count();

        if ($totalStudents === 0) {
            return 0;
        }

        $today = Carbon::today();

        // Count distinct students who attended today
        $presentStudents = $this->attendances()
            ->whereDate('date', $today)
            ->distinct('card_id')
            ->count('card_id');

        return round(($presentStudents / $totalStudents) * 100, 1);
    }

    // Relationships
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }


}
