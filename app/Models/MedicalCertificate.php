<?php
// app/Models/MedicalCertificate.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Login;

class MedicalCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'course_id',
        'absence_date',
        'reason',
        'file_path',
        'original_filename',
        'file_type',
        'file_size',
        'uploaded_at',
        'uploaded_by'
    ];

    protected $casts = [
        'absence_date' => 'date',
        'uploaded_at' => 'datetime',
        'file_size' => 'integer'
    ];

    // Relationships
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class, 'uploaded_by');
    }

    // Accessors
    public function getFileUrlAttribute()
    {
        if (!$this->file_path) {
            return null;
        }

        return Storage::url($this->file_path);
    }

    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) {
            return null;
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileIconAttribute()
    {
        if (!$this->file_type) {
            return 'mdi-file';
        }

        switch ($this->file_type) {
            case 'application/pdf':
                return 'mdi-file-pdf';
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/png':
                return 'mdi-file-image';
            default:
                return 'mdi-file';
        }
    }

    // Methods
    public function deleteFile()
    {
        if ($this->file_path && Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }
    }

    protected static function boot()
    {
        parent::boot();

        // Delete file when model is deleted
        static::deleting(function ($medicalCertificate) {
            $medicalCertificate->deleteFile();
        });
    }
}
