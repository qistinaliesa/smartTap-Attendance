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

                        {{-- MC/Reason Upload Modal --}}
                        <div class="modal fade" id="mcUploadModal" tabindex="-1" role="dialog" aria-labelledby="mcUploadModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-warning text-white">
                                        <h5 class="modal-title" id="mcUploadModalLabel">
                                            <i class="mdi mdi-file-upload"></i> Upload MC/Reason for Absence
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form id="mcUploadForm" enctype="multipart/form-data">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="text-center mb-3">
                                                <div class="avatar-lg mx-auto mb-2" id="mcStudentAvatar">
                                                    <div class="avatar-title bg-warning text-white rounded-circle" style="width: 60px; height: 60px; font-size: 1.5rem; display: flex; align-items: center; justify-content: center;">
                                                        S
                                                    </div>
                                                </div>
                                                <h6 class="mb-1" id="mcStudentName">Student Name</h6>
                                                <small class="text-muted" id="mcStudentMatricId">Matric ID</small>
                                            </div>

                                            <div class="form-groumarkMedical Certificates Sectionp">
                                                <label for="mcAbsentDate">Select Absent Date:</label>
                                                <select class="form-control" id="mcAbsentDate" name="date" required>
                                                    <option value="">Choose a date...</option>
                                                </select>
                                                <small class="text-muted">Only dates where the student was absent will be shown</small>
                                            </div>

                                            <div class="form-group">
                                                <label for="mcReason">Reason for Absence:</label>
                                                <textarea class="form-control" id="mcReason" name="reason" rows="3" placeholder="Enter reason for absence (e.g., Medical Certificate, Family emergency, etc.)" required></textarea>
                                            </div>

                                            <div class="form-group">
                                                <label for="mcFile">Upload Supporting Document (Optional):</label>
                                                <input type="file" class="form-control-file" id="mcFile" name="mc_file" accept=".jpg,.jpeg,.png,.pdf">
                                                <small class="text-muted">Accepted formats: JPG, PNG, PDF (Max: 2MB)</small>
                                            </div>

                                            <div class="alert alert-info">
                                                <i class="mdi mdi-information"></i>
                                                <strong>Note:</strong> Uploading MC/reason will automatically mark the student as present for the selected date.
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-warning">
                                                <i class="mdi mdi-upload"></i> Upload & Mark Present
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Student Profile Modal --}}
                        <div class="modal fade" id="studentProfileModal" tabindex="-1" role="dialog" aria-labelledby="studentProfileModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="studentProfileModalLabel">
                                            <i class="mdi mdi-account-circle"></i> Student Profile
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            {{-- Student Avatar and Basic Info --}}
                                            <div class="col-md-4 text-center">
                                                <div class="mb-3">
                                                    <div class="avatar-lg mx-auto mb-3" id="studentAvatar">
                                                        <div class="avatar-title bg-primary text-white rounded-circle" style="width: 80px; height: 80px; font-size: 2rem; display: flex; align-items: center; justify-content: center;">
                                                            A
                                                        </div>
                                                    </div>
                                                    <h5 class="mb-1" id="studentName">Student Name</h5>
                                                    <p class="text-muted mb-0" id="studentMatricId">Matric ID</p>
                                                    <span class="badge bg-info" id="studentCardUid">Card UID</span>
                                                </div>
                                            </div>

                                            {{-- Course Information --}}
                                            <div class="col-md-8">
                                                <h6 class="text-muted mb-3">Course Information</h6>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <strong>Course:</strong> {{ $course->course_code }}
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Section:</strong> {{ $course->section }}
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <strong>Credit Hours:</strong> {{ $course->credit_hours }}
                                                    </div>
                                                    <div class="col-6">
                                                        <strong>Enrolled Date:</strong> <span id="enrolledDate">Date</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Attendance Statistics --}}
                                        <hr>
                                        <h6 class="text-muted mb-3">Attendance Statistics</h6>
                                        <div class="row text-center mb-4">
                                            <div class="col-3">
                                                <div class="card bg-light">
                                                    <div class="card-body py-3">
                                                        <h4 class="text-success mb-1" id="attendanceRate">0%</h4>
                                                        <small>Attendance Rate</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="card bg-light">
                                                    <div class="card-body py-3">
                                                        <h4 class="text-primary mb-1" id="classesAttended">0</h4>
                                                        <small>Classes Attended</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="card bg-light">
                                                    <div class="card-body py-3">
                                                        <h4 class="text-warning mb-1" id="totalAbsences">0</h4>
                                                        <small>Absences</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="card bg-light">
                                                    <div class="card-body py-3">
                                                        <h4 class="text-info mb-1" id="totalClasses">0</h4>
                                                        <small>Total Classes</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Attendance Progress Bar --}}
                                        <div class="mb-4">
                                            <label class="form-label">Attendance Progress</label>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar" role="progressbar" id="attendanceProgressBar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </div>
                                         {{-- Medical Certificates Section --}}
                        <h6 class="text-muted mb-3">Medical Certificates / Excuses</h6>
                        <div id="medicalCertificates" class="mb-4">
                            <p class="text-center text-muted">Loading MC records...</p>
                        </div>

                                        {{-- Recent Attendance Records --}}
                                        <h6 class="text-muted mb-3">Recent Attendance Records</h6>
                                        <div id="recentAttendanceRecords">
                                            <p class="text-center text-muted">Loading attendance records...</p>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-primary" id="viewFullAttendanceBtn">
                                            <i class="mdi mdi-calendar-check"></i> View Full Attendance
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
                                                        <button type="button" class="btn btn-outline-primary" title="View Profile"
        onclick="showStudentProfile({{ json_encode([
            'enrollment_id' => $enrollment->id,
            'name' => $enrollment->card->name ?? 'N/A',
            'matric_id' => $enrollment->card->matric_id ?? 'N/A',
            'card_uid' => $enrollment->card->uid ?? 'N/A',
            'enrolled_at' => $enrollment->enrolled_at ? \Carbon\Carbon::parse($enrollment->enrolled_at)->format('M d, Y') : 'N/A',
            'attendance_percentage' => $attendancePercentage,
            'attendance_count' => $studentData['attendance_count'],
            'absences' => $absences,
            'status' => $status,
            'total_classes' => $studentData['total_classes'] ?? $attendanceStats['total_classes']
        ]) }})">
    <i class="mdi mdi-account-circle"></i>
</button>
                                                        @if($absences > 0)
                                                            <button type="button" class="btn btn-outline-warning" title="Upload MC/Reason" onclick="showMcUploadModal({{ json_encode([
                'enrollment_id' => $enrollment->id,
                'name' => $enrollment->card->name ?? 'N/A',
                'matric_id' => $enrollment->card->matric_id ?? 'N/A'
            ]) }})">
                                                                <i class="mdi mdi-file-upload"></i>
                                                            </button>
                                                        @endif
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

.avatar-lg {
    width: 80px;
    height: 80px;
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

/* Custom styles for the modal */
.modal-lg {
    max-width: 900px;
}

.modal-header.bg-primary {
    border-bottom: none;
}

.modal-header.bg-warning {
    border-bottom: none;
}

.attendance-record {
    border-left: 3px solid #dee2e6;
    padding-left: 15px;
    margin-bottom: 15px;
}

.attendance-record.present {
    border-left-color: #28a745;
}

.attendance-record.absent {
    border-left-color: #dc3545;
}

.attendance-record-date {
    font-weight: 600;
    color: #495057;
}

.attendance-record-time {
    font-size: 0.875rem;
    color: #6c757d;
}

.attendance-status {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
}

/* Loading spinner */
.loading-spinner {
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3498db;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    animation: spin 2s linear infinite;
    display: inline-block;
    margin-right: 10px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
/* Medical Certificate Card Styles */
.border-left-warning {
    border-left: 4px solid #ffc107;
}

.card-body.py-3 {
    padding-top: 1rem;
    padding-bottom: 1rem;
}

#medicalCertificates .card {
    box-shadow: 0 1px 3px rgba(0,0,0,.12), 0 1px 2px rgba(0,0,0,.24);
    transition: box-shadow 0.3s cubic-bezier(.25,.8,.25,1);
}

#medicalCertificates .card:hover {
    box-shadow: 0 3px 6px rgba(0,0,0,.16), 0 3px 6px rgba(0,0,0,.23);
}

/* File icon styling */
.mdi-file-pdf {
    color: #dc3545 !important;
}

.mdi-file-image {
    color: #28a745 !important;
}

.mdi-file {
    color: #6c757d !important;
}
</style>

<script>
// Global variable to store current student data for MC upload
let currentMcStudent = null;

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

// Function to show student profile modal
// Updated function to show student profile modal
function showStudentProfile(studentData) {
    console.log('Showing profile for student:', studentData);

    // Update modal content with student data
    document.getElementById('studentName').textContent = studentData.name;
    document.getElementById('studentMatricId').textContent = studentData.matric_id;
    document.getElementById('studentCardUid').textContent = studentData.card_uid;
    document.getElementById('enrolledDate').textContent = studentData.enrolled_at;

    // Update attendance statistics
    const attendancePercentage = studentData.attendance_percentage;
    const attendanceCount = studentData.attendance_count;
    const totalAbsences = studentData.absences;
    const totalClasses = studentData.total_classes || {{ $attendanceStats['total_classes'] ?? 1 }};

    // Update attendance statistics - these should now match the table
    document.getElementById('attendanceRate').textContent = attendancePercentage + '%';
    document.getElementById('classesAttended').textContent = attendanceCount;
    document.getElementById('totalAbsences').textContent = totalAbsences;
    document.getElementById('totalClasses').textContent = totalClasses;

    // Update avatar
    const avatarElement = document.querySelector('#studentAvatar .avatar-title');
    avatarElement.textContent = studentData.name.charAt(0).toUpperCase();

    // Update progress bar with SAME percentage as shown in table
    const progressBar = document.getElementById('attendanceProgressBar');
    progressBar.style.width = attendancePercentage + '%';
    progressBar.textContent = attendancePercentage + '%';
    progressBar.setAttribute('aria-valuenow', attendancePercentage);

    // Set progress bar color based on status (same as table)
    progressBar.className = 'progress-bar';
    if (studentData.status === 'good') {
        progressBar.classList.add('bg-success');
    } else if (studentData.status === 'cautious') {
        progressBar.classList.add('bg-warning');
    } else {
        progressBar.classList.add('bg-danger');
    }

    // Load recent attendance records via AJAX
    loadRecentAttendanceRecords(studentData.enrollment_id);

    // Load medical certificates via AJAX - THIS WAS MISSING!
    loadMedicalCertificates(studentData.enrollment_id);

    // Update the "View Full Attendance" button
    document.getElementById('viewFullAttendanceBtn').onclick = function() {
        viewStudentAttendance(studentData.enrollment_id);
    };

    // Show the modal
    $('#studentProfileModal').modal('show');
}

// Function to load recent attendance records
function loadRecentAttendanceRecords(enrollmentId) {
    const courseId = {{ $course->id }};

    // Show loading message
    document.getElementById('recentAttendanceRecords').innerHTML = '<p class="text-center text-muted">Loading attendance records...</p>';

    // Make AJAX request to fetch recent attendance records
    fetch(`/lecturer/courses/${courseId}/student/${enrollmentId}/recent-attendance`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            displayRecentAttendanceRecords(data);
        })
        .catch(error => {
            console.error('Error loading attendance records:', error);
            document.getElementById('recentAttendanceRecords').innerHTML =
                '<p class="text-center text-muted">Error loading attendance records. Using sample data.</p>';

            // Show sample data as fallback
            displaySampleAttendanceRecords();
        });
}

// Function to display recent attendance records
function displayRecentAttendanceRecords(attendanceData) {
    const container = document.getElementById('recentAttendanceRecords');

    if (attendanceData.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No attendance records found.</p>';
        return;
    }

    let html = '';
    attendanceData.slice(0, 5).forEach(record => {
        const statusClass = record.status === 'Present' ? 'present' : 'absent';
        const statusBadgeClass = record.status === 'Present' ? 'bg-success' : 'bg-danger';

        html += `
            <div class="attendance-record ${statusClass}">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="attendance-record-date">${record.date}</div>
                        <div class="attendance-record-time">Time In: ${record.time_in || 'N/A'}</div>
                    </div>
                    <span class="badge ${statusBadgeClass} attendance-status">${record.status}</span>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Function to display sample attendance records (fallback)
function displaySampleAttendanceRecords() {
    const sampleData = [
        { date: 'Aug 25, 2025', time_in: '09:15 AM', status: 'Present' },
        { date: 'Aug 23, 2025', time_in: '09:20 AM', status: 'Present' },
        { date: 'Aug 21, 2025', time_in: null, status: 'Absent' },
        { date: 'Aug 19, 2025', time_in: '09:10 AM', status: 'Present' },
        { date: 'Aug 17, 2025', time_in: '09:25 AM', status: 'Present' }
    ];

    displayRecentAttendanceRecords(sampleData);
}

// NEW: Function to show MC upload modal
function showMcUploadModal(studentData) {
    console.log('Showing MC upload modal for student:', studentData);
    currentMcStudent = studentData;

    // Update modal content with student data
    document.getElementById('mcStudentName').textContent = studentData.name;
    document.getElementById('mcStudentMatricId').textContent = studentData.matric_id;

    // Update avatar
    const avatarElement = document.querySelector('#mcStudentAvatar .avatar-title');
    avatarElement.textContent = studentData.name.charAt(0).toUpperCase();

    // Reset form
    document.getElementById('mcUploadForm').reset();
    document.getElementById('mcAbsentDate').innerHTML = '<option value="">Loading dates...</option>';

    // Load absent dates for this student
    loadAbsentDates(studentData.enrollment_id);

    // Show the modal
    $('#mcUploadModal').modal('show');
}

// NEW: Function to load absent dates for a student
function loadAbsentDates(enrollmentId) {
    const courseId = {{ $course->id }};

    fetch(`/lecturer/courses/${courseId}/student/${enrollmentId}/absent-dates`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            const selectElement = document.getElementById('mcAbsentDate');
            selectElement.innerHTML = '<option value="">Choose a date...</option>';

            if (data.length === 0) {
                selectElement.innerHTML = '<option value="">No absent dates found</option>';
                return;
            }

            data.forEach(dateInfo => {
                const option = document.createElement('option');
                option.value = dateInfo.value;
                option.textContent = dateInfo.label;
                selectElement.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading absent dates:', error);
            document.getElementById('mcAbsentDate').innerHTML = '<option value="">Error loading dates</option>';
        });
}

// NEW: Handle MC upload form submission
document.addEventListener('DOMContentLoaded', function() {
    const mcForm = document.getElementById('mcUploadForm');
    if (mcForm) {
        mcForm.addEventListener('submit', function(e) {
            e.preventDefault();
            handleMcUpload();
        });
    }
});

// NEW: Function to handle MC upload
// Simplified version for debugging
// Replace your existing handleMcUpload function with this one:

function handleMcUpload() {
    console.log('handleMcUpload called');

    if (!currentMcStudent) {
        alert('No student selected. Please try again.');
        return;
    }

    // Get form values
    const selectedDate = document.getElementById('mcAbsentDate').value;
    const reason = document.getElementById('mcReason').value;
    const fileInput = document.getElementById('mcFile');

    console.log('Selected date:', selectedDate);
    console.log('Reason:', reason);
    console.log('File input:', fileInput);
    console.log('Has file:', fileInput.files.length > 0);

    if (fileInput.files.length > 0) {
        console.log('File details:', {
            name: fileInput.files[0].name,
            size: fileInput.files[0].size,
            type: fileInput.files[0].type
        });
    }

    if (!selectedDate || !reason.trim()) {
        alert('Please fill in all required fields.');
        return;
    }

    const courseId = {{ $course->id }};
    const enrollmentId = currentMcStudent.enrollment_id;
    const url = `/lecturer/courses/${courseId}/student/${enrollmentId}/mark-present`;

    console.log('URL:', url);
    console.log('Course ID:', courseId);
    console.log('Enrollment ID:', enrollmentId);

    // Show loading state
    const submitBtn = document.querySelector('#mcUploadForm button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Uploading...';
    submitBtn.disabled = true;

    // FIXED: Use FormData to properly handle file uploads
    const formData = new FormData();
    formData.append('date', selectedDate);
    formData.append('reason', reason.trim());
    formData.append('_token', '{{ csrf_token() }}');

    // Add file if selected
    if (fileInput.files.length > 0) {
        formData.append('mc_file', fileInput.files[0]);
        console.log('File added to FormData:', fileInput.files[0].name);
    }

    // DEBUG: Log FormData contents
    console.log('FormData contents:');
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
            // DON'T set Content-Type when using FormData - let browser set it automatically
        },
        body: formData // Use FormData instead of JSON
    })
    .then(response => {
        console.log('Response received:', response);
        console.log('Status:', response.status);
        console.log('Status text:', response.statusText);

        return response.text(); // Get as text first to see what we're getting
    })
    .then(text => {
        console.log('Response text:', text);

        try {
            const data = JSON.parse(text);
            console.log('Parsed JSON:', data);

            if (data.success) {
                alert('Success: ' + data.message);
                if (data.debug) {
                    console.log('Upload debug info:', data.debug);
                }
                $('#mcUploadModal').modal('hide');
                location.reload(); // Refresh to see the updated data
            } else {
                alert('Error: ' + (data.error || 'Unknown error'));
            }
        } catch (e) {
            console.error('JSON parse error:', e);
            alert('Server returned invalid response. Check console for details.');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Network error: ' + error.message);
    })
    .finally(() => {
        submitBtn.innerHTML = originalBtnText;
        submitBtn.disabled = false;
    });
}

// Document ready function
$(document).ready(function() {
    console.log('Document is ready');
    console.log('jQuery version:', typeof $ !== 'undefined' ? $.fn.jquery : 'jQuery not loaded');

    // Add CSRF token to meta tag if not already present
    if (!document.querySelector('meta[name="csrf-token"]')) {
        const metaTag = document.createElement('meta');
        metaTag.setAttribute('name', 'csrf-token');
        metaTag.setAttribute('content', '{{ csrf_token() }}');
        document.getElementsByTagName('head')[0].appendChild(metaTag);
    }

    // Add tooltips to progress bars
    $('.progress').each(function() {
        const percentage = $(this).find('.progress-bar').attr('aria-valuenow');
        $(this).attr('title', `Attendance: ${percentage}%`);
    });

    // Handle file input change
    $('#mcFile').on('change', function() {
        const file = this.files[0];
        if (file) {
            const maxSize = 2 * 1024 * 1024; // 2MB
            if (file.size > maxSize) {
                alert('File size too large. Please select a file smaller than 2MB.');
                this.value = '';
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                alert('Invalid file type. Please select a JPG, PNG, or PDF file.');
                this.value = '';
                return;
            }
        }
    });

});
// Function to load medical certificates for a student
function loadMedicalCertificates(enrollmentId) {
    const courseId = {{ $course->id }};

    // Show loading message
    document.getElementById('medicalCertificates').innerHTML = '<p class="text-center text-muted">Loading MC records...</p>';

    // Make AJAX request to fetch medical certificates
    fetch(`/lecturer/courses/${courseId}/student/${enrollmentId}/medical-certificates`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            displayMedicalCertificates(data);
        })
        .catch(error => {
            console.error('Error loading medical certificates:', error);
            document.getElementById('medicalCertificates').innerHTML =
                '<p class="text-center text-muted">Error loading MC records.</p>';
        });
}

// Function to display medical certificates
function displayMedicalCertificates(medicalCertificates) {
    const container = document.getElementById('medicalCertificates');

    if (medicalCertificates.length === 0) {
        container.innerHTML = '<p class="text-center text-muted">No medical certificates found.</p>';
        return;
    }

    let html = '<div class="row">';

    medicalCertificates.forEach(mc => {
        const hasFile = mc.has_file;
        const fileIcon = mc.file_icon || 'mdi-file';

        html += `
            <div class="col-12 mb-3">
                <div class="card border-left-warning">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">${mc.date}</h6>
                                <p class="text-muted mb-2">${mc.reason}</p>
                                <small class="text-muted">Uploaded: ${mc.uploaded_at}</small>
                            </div>
                            <div class="text-right">
                                ${hasFile ? `
                                    <div class="mb-2">
                                        <i class="mdi ${fileIcon} text-primary" style="font-size: 1.5rem;"></i>
                                        <br>
                                        <small class="text-muted">${mc.file_size}</small>
                                    </div>
                                    <a href="${mc.file_url}" target="_blank" class="btn btn-outline-primary btn-sm">
                                        <i class="mdi mdi-eye"></i> View
                                    </a>
                                ` : `
                                    <span class="badge badge-secondary">No File</span>
                                `}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
}
</script>
@endsection
