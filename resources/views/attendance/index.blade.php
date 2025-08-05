@extends('master.userlayout')

@section('content')
<div class="container mt-4">
    <h1>Attendance Records</h1>
    <p>Showing all attendance records from the RFID system</p>

    <div class="row mt-4">
        <div class="col-md-12">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="font-weight-bold">No</th>
                        <th class="font-weight-bold">Name</th>
                        <th class="font-weight-bold">Matric ID</th>
                        <th class="font-weight-bold">Date</th>
                        <th class="font-weight-bold">Time In</th>
                        <th class="font-weight-bold">Time Out</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($formattedAttendances as $index => $attendance)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $attendance['name'] }}</td>
                            <td>{{ $attendance['matric_id'] }}</td>
                            <td>{{ $attendance['date'] }}</td>
                            <td>{{ $attendance['time_in'] }}</td>
                            <td>{{ $attendance['time_out'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No attendance records found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Student Count Display -->
            <div class="mt-3">
                <div class="alert alert-info">
                    <strong>Students Present:</strong> {{ count($formattedAttendances) }}{{ isset($totalStudents) ? '/' . $totalStudents : '' }}
                </div>
            </div>

            <!-- Alternative: Simple text display -->
            {{--
            <div class="mt-3 text-right">
                <p class="text-muted">
                    <strong>Students Present: {{ count($formattedAttendances) }}</strong>
                </p>
            </div>
            --}}

            <!-- Alternative: Badge style -->
            {{--
            <div class="mt-3 text-center">
                <span class="badge badge-primary badge-lg">
                    Total Students: {{ count($formattedAttendances) }}
                </span>
            </div>
            --}}
        </div>
    </div>
</div>
@endsection
