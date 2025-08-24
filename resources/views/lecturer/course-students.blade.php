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
                                <!-- CHANGED: Take Attendance instead of View Attendance -->
                                <button type="button" class="btn btn-outline-success btn-sm" data-toggle="modal" data-target="#attendanceDateModal">
                                    <i class="mdi mdi-calendar-plus"></i> Take Attendance
                                </button>
                                <a href="{{ route('lecturer.courses') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="mdi mdi-arrow-left"></i> Back to Courses
                                </a>
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
                                        <!-- CHANGED: Take Attendance button -->
                                        <button type="button" class="btn btn-success" onclick="takeAttendance()">
                                            <i class="mdi mdi-calendar-plus"></i> Take Attendance
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($enrolledStudents->count() > 0)
                            {{-- Students Table --}}
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Student Name</th>
                                            <th>Matric ID</th>
                                            <th>Card UID</th>
                                            <th>Enrolled Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($enrolledStudents as $index => $enrollment)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
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
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Summary Card --}}
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-primary mb-1">{{ $enrolledStudents->count() }}</h3>
                                            <p class="text-muted mb-0">Total Students</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-success mb-1">{{ $course->credit_hours }}</h3>
                                            <p class="text-muted mb-0">Credit Hours</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h3 class="text-info mb-1">{{ $course->section }}</h3>
                                            <p class="text-muted mb-0">Section</p>
                                        </div>
                                    </div>
                                </div>
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
</style>

<script>
// CHANGED: Function to take attendance (redirect to attendance page)
function takeAttendance() {
    console.log('takeAttendance function called');

    const selectedDate = document.getElementById('attendanceDate').value;
    const courseId = {{ $course->id }};

    console.log('Selected date:', selectedDate);
    console.log('Course ID:', courseId);

    if (selectedDate) {
        // CHANGED: Redirect to the attendance page to take attendance
        const url = '/lecturer/courses/' + courseId + '/take-attendance?date=' + selectedDate;
        console.log('Navigating to:', url);
        window.location.href = url;
    } else {
        alert('Please select a date first.');
    }
}

// NEW: Function to view individual student attendance history
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
});
</script>
@endsection
