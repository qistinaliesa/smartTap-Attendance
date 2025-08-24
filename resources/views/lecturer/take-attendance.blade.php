@extends('master.userlayout')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        {{-- Header Section --}}
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="card-title mb-1">
                                    <i class="mdi mdi-calendar-check text-success"></i>
                                    Take Attendance - {{ $course->course_code }}
                                </h4>
                                <p class="text-muted mb-0">
                                    Section {{ $course->section }} | {{ $course->credit_hours }} Credit Hours
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('lecturer.course.show', $course->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="mdi mdi-arrow-left"></i> Back to Course
                                </a>
                            </div>
                        </div>

                        {{-- Attendance Summary Cards --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <i class="mdi mdi-account-check" style="font-size: 2rem;"></i>
                                        <h3 class="mt-2 mb-1">{{ count($formattedAttendances) }}</h3>
                                        <p class="mb-0">Students Present</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <i class="mdi mdi-account-multiple" style="font-size: 2rem;"></i>
                                        <h3 class="mt-2 mb-1">{{ $totalStudents }}</h3>
                                        <p class="mb-0">Total Enrolled</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <i class="mdi mdi-account-remove" style="font-size: 2rem;"></i>
                                        <h3 class="mt-2 mb-1">{{ $totalStudents - count($formattedAttendances) }}</h3>
                                        <p class="mb-0">Absent</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Attendance Records Table --}}
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="font-weight-bold">#</th>
                                        <th class="font-weight-bold">
                                            <i class="mdi mdi-account"></i> Name
                                        </th>
                                        <th class="font-weight-bold">
                                            <i class="mdi mdi-card-account-details"></i> Matric ID
                                        </th>
                                        <th class="font-weight-bold">
                                            <i class="mdi mdi-calendar"></i> Date
                                        </th>
                                        <th class="font-weight-bold">
                                            <i class="mdi mdi-clock-in"></i> Time In
                                        </th>
                                        <th class="font-weight-bold">
                                            <i class="mdi mdi-clock-out"></i> Time Out
                                        </th>
                                        <th class="font-weight-bold">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($formattedAttendances as $index => $attendance)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm me-2">
                                                        <div class="avatar-title bg-success text-white rounded-circle">
                                                            {{ strtoupper(substr($attendance['name'], 0, 1)) }}
                                                        </div>
                                                    </div>
                                                    <span class="font-weight-medium">{{ $attendance['name'] }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $attendance['matric_id'] }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $attendance['date'] }}</small>
                                            </td>
                                            <td>
                                                <span class="text-success font-weight-bold">
                                                    <i class="mdi mdi-clock-in"></i> {{ $attendance['time_in'] }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($attendance['time_out'])
                                                    <span class="text-danger font-weight-bold">
                                                        <i class="mdi mdi-clock-out"></i> {{ $attendance['time_out'] }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <i class="mdi mdi-check"></i> Present
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-muted">
                                                    <i class="mdi mdi-information-outline" style="font-size: 2rem;"></i>
                                                    <h5 class="mt-2">No Attendance Records</h5>
                                                    <p>No students have checked in for this date yet.</p>
                                                    <small>Students can tap their cards on the RFID reader to mark attendance.</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Instructions Card --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0">
                                            <i class="mdi mdi-information"></i>
                                            How to Take Attendance
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6><i class="mdi mdi-numeric-1-circle text-primary"></i> Automatic Tracking</h6>
                                                <p class="text-muted mb-3">
                                                    Students tap their RFID cards on the reader to automatically mark attendance.
                                                    Records will appear in real-time on this page.
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6><i class="mdi mdi-numeric-2-circle text-primary"></i> Monitor Status</h6>
                                                <p class="text-muted mb-3">
                                                    Refresh this page periodically to see updated attendance records.
                                                    The summary cards show present, total, and absent counts.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-center mt-3">
                                            <button onclick="window.location.reload()" class="btn btn-primary btn-sm">
                                                <i class="mdi mdi-refresh"></i> Refresh Records
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 35px;
    height: 35px;
}

.avatar-title {
    width: 35px;
    height: 35px;
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
    background-color: #f8f9fa;
}

.d-flex.gap-2 > * + * {
    margin-left: 0.5rem;
}

.font-weight-medium {
    font-weight: 500;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,.1);
    border: none;
}

.badge {
    font-size: 0.75em;
}
</style>

<script>
$(document).ready(function() {
    console.log('Take Attendance page loaded');
    console.log('Total Students: {{ $totalStudents }}');
    console.log('Present Students: {{ count($formattedAttendances) }}');

    // Auto-refresh every 30 seconds to show new attendance records
    setInterval(function() {
        console.log('Auto-refreshing attendance records...');
        window.location.reload();
    }, 30000); // 30 seconds
});
</script>
@endsection
