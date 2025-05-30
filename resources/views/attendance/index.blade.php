@extends('master.layout')

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
        </div>
    </div>
</div>
@endsection

