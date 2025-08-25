@extends('master.userlayout')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        {{-- Course Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="card-title mb-1">{{ $course->course_code }} - Students</h4>
                                <p class="text-muted mb-0">Section {{ $course->section }} | {{ $course->credit_hours }} Credit Hours</p>
                            </div>
                            <div class="d-flex gap-2">
                                <!-- Take Attendance Button -->
                                <button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#attendanceDateModal">
                                    <i class="mdi mdi-calendar-plus"></i> Take Attendance
                                </button>
                                <a href="{{ route('lecturer.courses') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="mdi mdi-arrow-left"></i> Back to Courses
                                </a>
                            </div>
                        </div>

                     {{-- Attendance Statistics Summary --}}
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="mdi mdi-account-multiple" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1">{{ $attendanceStats['total_students'] }}</h3>
                <p class="mb-0">Total Students</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="mdi mdi-chart-line" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1">{{ $attendanceStats['average_attendance'] }}%</h3>
                <p class="mb-0">Average Attendance</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="mdi mdi-alert-triangle" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1">{{ $attendanceStats['total_warnings'] }}</h3>
                <p class="mb-0">Warnings</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body text-center d-flex flex-column justify-content-center">
                <i class="mdi mdi-calendar-check" style="font-size: 2rem;"></i>
                <h3 class="mt-2 mb-1">{{ $attendanceStats['total_classes'] }}</h3>
                <p class="mb-0">Classes Held</p>
            </div>
        </div>
    </div>
</div>

                        {{-- Legend --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-end">
                                    <div class="d-flex align-items-center me-3">
                                        <div class="badge bg-success me-1">■</div>
                                        <small>Good (≥75%)</small>
                                    </div>
                                    <div class="d-flex align-items-center me-3">
                                        <div class="badge bg-warning me-1">■</div>
                                        <small>Cautious (50-74%)</small>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="badge bg-danger me-1">■</div>
                                        <small>Critical (<50%)</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Date Picker Modal --}}
                        <div class="modal fade" id="attendanceDateModal" tabindex="-1" role="dialog" aria-labelledby="attendanceDateModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="attendanceDateModalLabel">Take Attendance</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="attendanceDate">Choose Date:</label>
                                            <input type="date" class="form-control" id="attendanceDate" value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="text-center">
                                            <small class="text-muted">Select a date to take attendance for {{ $course->course_code }}</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                        <button type="button" class="btn btn-success" onclick="takeAttendance()">
                                            <i class="mdi mdi-calendar-plus"></i> Take Attendance
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(count($studentsWithAttendance) > 0)
                            {{-- Students Table with Attendance Percentages --}}
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No.</th>
                                            <th>Student Name</th>
                                            <th>Matric ID</th>
                                            <th>Card UID</th>
                                            <th>Attendance (%)</th>
                                            <th>Absences</th>
                                            <th>Enrolled Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($studentsWithAttendance as $studentData)
                                            @php
                                                $enrollment = $studentData['enrollment'];
                                                $attendancePercentage = $studentData['attendance_percentage'];
                                                $status = $studentData['status'];
                                                $hasWarning = $studentData['has_warning'];
                                                $absences = $studentData['absences'];
                                            @endphp
                                            <tr class="{{ $hasWarning ? 'table-warning' : '' }}">
                                                <td>{{ $studentData['index'] }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-3">
                                                            <div class="avatar-title bg-primary text-white rounded-circle">
                                                                {{ strtoupper(substr($enrollment->card->name ?? 'N', 0, 1)) }}
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0">{{ $enrollment->card->name ?? 'N/A' }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info text-dark">{{ $enrollment->card->matric_id ?? 'N/A' }}</span>
                                                </td>
                                                <td>
                                                    <code>{{ $enrollment->card->uid ?? 'N/A' }}</code>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-2" style="height: 20px; width: 100px;">
                                                            @if($status == 'good')
                                                                <div class="progress-bar bg-success" role="progressbar"
                                                                     style="width: {{ $attendancePercentage }}%"
                                                                     aria-valuenow="{{ $attendancePercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                                </div>
                                                            @elseif($status == 'cautious')
                                                                <div class="progress-bar bg-warning" role="progressbar"
                                                                     style="width: {{ $attendancePercentage }}%"
                                                                     aria-valuenow="{{ $attendancePercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                                </div>
                                                            @else
                                                                <div class="progress-bar bg-danger" role="progressbar"
                                                                     style="width: {{ $attendancePercentage }}%"
                                                                     aria-valuenow="{{ $attendancePercentage }}" aria-valuemin="0" aria-valuemax="100">
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <span class="badge {{ $status == 'good' ? 'badge-success' : ($status == 'cautious' ? 'badge-warning' : 'badge-danger') }}">
                                                            {{ $attendancePercentage }}%
                                                        </span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-{{ $absences > 3 ? 'danger' : ($absences > 1 ? 'warning' : 'success') }} font-weight-bold">
                                                        {{ $absences }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $enrollment->enrolled_at ? \Carbon\Carbon::parse($enrollment->enrolled_at)->format('M d, Y') : 'N/A' }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <button type="button" class="btn btn-outline-info" title="View Past Attendance" onclick="viewStudentAttendance({{ $enrollment->id }})">
                                                            <i class="mdi mdi-calendar-check"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary" title="View Profile">
                                                            <i class="mdi mdi-account-circle"></i>
                                                        </button>
                                                        @if($hasWarning)
                                                            <button type="button" class="btn btn-outline-warning btn-sm" title="Warning: Low Attendance">
                                                                <i class="mdi mdi-alert-triangle"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            {{-- No Students Enrolled --}}
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="mdi mdi-account-multiple-outline" style="font-size: 4rem; color: #ccc;"></i>
                                </div>
                                <h5 class="text-muted">No Students Enrolled</h5>
                                <p class="text-muted">There are currently no students enrolled in this course.</p>
                                <div class="mt-3">
                                    <small class="text-muted">Course: <strong>{{ $course->course_code }}</strong> | Section: <strong>{{ $course->section }}</strong></small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 40px;
    height: 40px;
}

.avatar-title {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group-sm > .btn, .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.d-flex.gap-2 > * + * {
    margin-left: 0.5rem;
}

.progress {
    background-color: #f8f9fa;
}

.progress-bar {
    transition: width 0.6s ease;
}

.table-warning {
    background-color: rgba(255, 193, 7, 0.1);
}

.badge {
    font-size: 0.75rem;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,.1);
    border: none;
}

.card-body {
    padding: 1.25rem;
}

.font-weight-bold {
    font-weight: 700 !important;
}

.me-1 {
    margin-right: 0.25rem;
}

.me-2 {
    margin-right: 0.5rem;
}

.me-3 {
    margin-right: 1rem;
}
</style>

<script>
// Function to take attendance (redirect to attendance page)
function takeAttendance() {
    console.log('takeAttendance function called');

    const selectedDate = document.getElementById('attendanceDate').value;
    const courseId = {{ $course->id }};

    console.log('Selected date:', selectedDate);
    console.log('Course ID:', courseId);

    if (selectedDate) {
        // Redirect to the attendance page to take attendance
        const url = '/lecturer/courses/' + courseId + '/take-attendance?date=' + selectedDate;
        console.log('Navigating to:', url);
        window.location.href = url;
    } else {
        alert('Please select a date first.');
    }
}

// Function to view individual student attendance history
function viewStudentAttendance(enrollmentId) {
    console.log('Viewing attendance for enrollment ID:', enrollmentId);
    const courseId = {{ $course->id }};
    const url = '/lecturer/courses/' + courseId + '/student/' + enrollmentId + '/attendance';
    window.location.href = url;
}

// Document ready function
$(document).ready(function() {
    console.log('Document is ready');
    console.log('jQuery version:', typeof $ !== 'undefined' ? $.fn.jquery : 'jQuery not loaded');

    // Add tooltips to progress bars
    $('.progress').each(function() {
        const percentage = $(this).find('.progress-bar').attr('aria-valuenow');
        $(this).attr('title', `Attendance: ${percentage}%`);
    });
});
</script>
@endsection
