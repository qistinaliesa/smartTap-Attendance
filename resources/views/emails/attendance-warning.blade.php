@component('mail::message')
# Attendance Warning

Dear {{ $studentName }} ({{ $studentMatric }}),

{!! nl2br(e($customMessage)) !!}

**Course Details:**
- **Course:** {{ $courseCode }}
- **Lecturer:** {{ $lecturerName }}

Please take necessary action to improve your attendance.

@endcomponent

