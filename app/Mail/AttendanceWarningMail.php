<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Address;


class AttendanceWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailData;

    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;

        // Debug: Log email construction
        Log::info('AttendanceWarningMail constructed with data:', $emailData);
    }

    public function envelope(): Envelope
{
    $subject = $this->emailData['subject'] ?? 'Attendance Warning';
    $fromAddress = config('mail.from.address', '2003nurnisanasuhanazri@gmail.com');
    $fromName = config('mail.from.name', 'SmartTap Attendance System');

    Log::info('Email envelope created:', [
        'subject' => $subject,
        'from_address' => $fromAddress,
        'from_name' => $fromName
    ]);

    return new Envelope(
        subject: $subject,
        from: new Address($fromAddress, $fromName),
    );
}


    public function content(): Content
    {
        $contentData = [
            'studentName'   => $this->emailData['student_name'] ?? 'Student',
            'studentMatric' => $this->emailData['student_matric'] ?? 'N/A',
            'customMessage' => $this->emailData['custom_message'] ?? 'Please improve your attendance.',
            'courseCode'    => $this->emailData['course_code'] ?? 'Unknown Course',
            'lecturerName'  => $this->emailData['lecturer_name'] ?? 'Lecturer',
        ];

        Log::info('Email content data:', $contentData);

        return new Content(
            markdown: 'emails.attendance-warning',
            with: $contentData
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
