
<?php



use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Class AbsenceSubmission
 *
 * Create this file at: app/Models/AbsenceSubmission.php
 */
class AbsenceSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'course_id',
        'absence_date',
        'absence_type',
        'reason',
        'document_path',
        'status',
        'lecturer_comment',
        'submitted_at',
        'reviewed_at',
        'submitted_by',
        'reviewed_by',
        'metadata'
    ];

    protected $casts = [
        'absence_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'metadata' => 'array'
    ];

    /**
     * Get the enrollment that owns the absence submission
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Get the course that owns the absence submission
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the lecturer who reviewed this submission
     */
    public function reviewer()
    {
        return $this->belongsTo(Lecturer::class, 'reviewed_by');
    }

    /**
     * Get the student through enrollment
     */
    public function student()
    {
        return $this->hasOneThrough(Card::class, Enrollment::class, 'id', 'id', 'enrollment_id', 'card_id');
    }

    /**
     * Scope to get pending submissions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved submissions
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get rejected submissions
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to get submissions for a specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->where('absence_date', $date);
    }

    /**
     * Scope to get submissions within date range
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('absence_date', [$startDate, $endDate]);
    }

    /**
     * Get the formatted absence type
     */
    public function getFormattedAbsenceTypeAttribute()
    {
        $types = [
            'medical' => 'Medical Certificate',
            'emergency' => 'Emergency',
            'family' => 'Family Matter',
            'official' => 'Official/University Business',
            'other' => 'Other'
        ];

        return $types[$this->absence_type] ?? ucfirst($this->absence_type);
    }

    /**
     * Get the status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger'
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Check if submission has a document
     */
    public function hasDocument()
    {
        return !empty($this->document_path);
    }

    /**
     * Get the document URL
     */
    public function getDocumentUrlAttribute()
    {
        if ($this->document_path) {
            return \Storage::url($this->document_path);
        }
        return null;
    }

    /**
     * Get the document file name
     */
    public function getDocumentNameAttribute()
    {
        if ($this->document_path) {
            return basename($this->document_path);
        }
        return null;
    }

    /**
     * Check if submission is still pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if submission is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if submission is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Approve the submission
     */
    public function approve($lecturerId, $comment = null)
    {
        return $this->update([
            'status' => 'approved',
            'lecturer_comment' => $comment,
            'reviewed_at' => now(),
            'reviewed_by' => $lecturerId
        ]);
    }

    /**
     * Reject the submission
     */
    public function reject($lecturerId, $comment = null)
    {
        return $this->update([
            'status' => 'rejected',
            'lecturer_comment' => $comment,
            'reviewed_at' => now(),
            'reviewed_by' => $lecturerId
        ]);
    }

    /**
     * Get formatted submitted date
     */
    public function getFormattedSubmittedDateAttribute()
    {
        return $this->submitted_at->format('M d, Y H:i A');
    }

    /**
     * Get formatted absence date
     */
    public function getFormattedAbsenceDateAttribute()
    {
        return $this->absence_date->format('M d, Y');
    }

    /**
     * Get days since submission
     */
    public function getDaysSinceSubmissionAttribute()
    {
        return $this->submitted_at->diffInDays(now());
    }

    /**
     * Check if submission is overdue for review (more than 3 days)
     */
    public function isOverdueForReview()
    {
        return $this->isPending() && $this->days_since_submission > 3;
    }

    /**
     * Boot method for model events
     */
    protected static function boot()
    {
        parent::boot();

        // Set submitted_at when creating
        static::creating(function ($submission) {
            if (!$submission->submitted_at) {
                $submission->submitted_at = now();
            }
        });

        // Clean up file when deleting
        static::deleting(function ($submission) {
            if ($submission->document_path) {
                \Storage::disk('public')->delete($submission->document_path);
            }
        });
    }
}
