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
                                {{-- Download PDF Button --}}
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="downloadPDF()"
                                        {{ count($formattedAttendances) == 0 ? 'disabled' : '' }}>
                                    <i class="mdi mdi-file-pdf"></i> Download PDF
                                </button>
                                {{-- Export Excel Button --}}
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportExcel()"
                                        {{ count($formattedAttendances) == 0 ? 'disabled' : '' }}>
                                    <i class="mdi mdi-file-excel"></i> Export Excel
                                </button>
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
                            <table class="table table-hover" id="attendanceTable">
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
                                            <div class="col-md-4">
                                                <h6><i class="mdi mdi-numeric-1-circle text-primary"></i> Automatic Tracking</h6>
                                                <p class="text-muted mb-3">
                                                    Students tap their RFID cards on the reader to automatically mark attendance.
                                                    Records will appear in real-time on this page.
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <h6><i class="mdi mdi-numeric-2-circle text-primary"></i> Monitor Status</h6>
                                                <p class="text-muted mb-3">
                                                    Refresh this page periodically to see updated attendance records.
                                                    The summary cards show present, total, and absent counts.
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <h6><i class="mdi mdi-numeric-3-circle text-primary"></i> Export Records</h6>
                                                <p class="text-muted mb-3">
                                                    Download attendance records as PDF or Excel files for your records.
                                                    Reports include course details and attendance summary.
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

{{-- Loading Modal --}}
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mb-0">Generating report...</p>
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

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.spinner-border {
    width: 2rem;
    height: 2rem;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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

// Function to download PDF
function downloadPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    // Show loading modal
    $('#loadingModal').modal('show');

    setTimeout(function() {
        try {
            // Course information
            const courseCode = '{{ $course->course_code }}';
            const section = '{{ $course->section }}';
            const creditHours = '{{ $course->credit_hours }}';
            const currentDate = new Date().toLocaleDateString();
            const attendanceDate = '{{ request()->get("date", date("Y-m-d")) }}';

            // Header
            doc.setFontSize(20);
            doc.setTextColor(40);
            doc.text('Attendance Report', 105, 20, { align: 'center' });

            // Course details
            doc.setFontSize(12);
            doc.setTextColor(60);
            doc.text(`Course: ${courseCode}`, 20, 35);
            doc.text(`Section: ${section}`, 20, 45);
            doc.text(`Credit Hours: ${creditHours}`, 20, 55);
            doc.text(`Date: ${attendanceDate}`, 120, 35);
            doc.text(`Generated: ${currentDate}`, 120, 45);

            // Summary statistics
            const presentCount = {{ count($formattedAttendances) }};
            const totalCount = {{ $totalStudents }};
            const absentCount = totalCount - presentCount;
            const attendanceRate = totalCount > 0 ? ((presentCount / totalCount) * 100).toFixed(1) : 0;

            doc.text(`Present: ${presentCount}`, 20, 70);
            doc.text(`Absent: ${absentCount}`, 70, 70);
            doc.text(`Total: ${totalCount}`, 120, 70);
            doc.text(`Attendance Rate: ${attendanceRate}%`, 20, 80);

            // Table data
            const tableData = [];
            @foreach ($formattedAttendances as $index => $attendance)
                tableData.push([
                    '{{ $index + 1 }}',
                    '{{ $attendance["name"] }}',
                    '{{ $attendance["matric_id"] }}',
                    '{{ $attendance["date"] }}',
                    '{{ $attendance["time_in"] }}',
                    '{{ $attendance["time_out"] ?? "-" }}',
                    'Present'
                ]);
            @endforeach

            // Create table
            if (tableData.length > 0) {
                doc.autoTable({
                    startY: 90,
                    head: [['#', 'Name', 'Matric ID', 'Date', 'Time In', 'Time Out', 'Status']],
                    body: tableData,
                    theme: 'striped',
                    headStyles: { fillColor: [52, 144, 220] },
                    styles: { fontSize: 10, cellPadding: 3 },
                    columnStyles: {
                        0: { cellWidth: 10 },
                        1: { cellWidth: 35 },
                        2: { cellWidth: 25 },
                        3: { cellWidth: 25 },
                        4: { cellWidth: 20 },
                        5: { cellWidth: 20 },
                        6: { cellWidth: 20 }
                    }
                });
            } else {
                doc.setFontSize(12);
                doc.setTextColor(150);
                doc.text('No attendance records found for this date.', 105, 100, { align: 'center' });
            }

            // Footer
            const pageHeight = doc.internal.pageSize.height;
            doc.setFontSize(8);
            doc.setTextColor(128);
            doc.text('Generated by SmartTap Attendance System', 105, pageHeight - 10, { align: 'center' });

            // Save the PDF
            const fileName = `Attendance_${courseCode}_${section}_${attendanceDate.replace(/\//g, '-')}.pdf`;
            doc.save(fileName);

        } catch (error) {
            console.error('Error generating PDF:', error);
            alert('Error generating PDF. Please try again.');
        } finally {
            // Hide loading modal
            $('#loadingModal').modal('hide');
        }
    }, 500);
}

// Function to export Excel
function exportExcel() {
    // Show loading modal
    $('#loadingModal').modal('show');

    setTimeout(function() {
        try {
            const courseCode = '{{ $course->course_code }}';
            const section = '{{ $course->section }}';
            const attendanceDate = '{{ request()->get("date", date("Y-m-d")) }}';

            // Prepare data
            const excelData = [];

            // Add headers with course info
            excelData.push(['Course Code:', courseCode]);
            excelData.push(['Section:', section]);
            excelData.push(['Credit Hours:', '{{ $course->credit_hours }}']);
            excelData.push(['Date:', attendanceDate]);
            excelData.push(['Generated:', new Date().toLocaleString()]);
            excelData.push([]);

            // Add summary
            excelData.push(['ATTENDANCE SUMMARY']);
            excelData.push(['Present:', {{ count($formattedAttendances) }}]);
            excelData.push(['Absent:', {{ $totalStudents - count($formattedAttendances) }}]);
            excelData.push(['Total Enrolled:', {{ $totalStudents }}]);
            excelData.push(['Attendance Rate:', '{{ $totalStudents > 0 ? round((count($formattedAttendances) / $totalStudents) * 100, 1) : 0 }}%']);
            excelData.push([]);

            // Add table headers
            excelData.push(['#', 'Name', 'Matric ID', 'Date', 'Time In', 'Time Out', 'Status']);

            // Add attendance data
            @foreach ($formattedAttendances as $index => $attendance)
                excelData.push([
                    {{ $index + 1 }},
                    '{{ $attendance["name"] }}',
                    '{{ $attendance["matric_id"] }}',
                    '{{ $attendance["date"] }}',
                    '{{ $attendance["time_in"] }}',
                    '{{ $attendance["time_out"] ?? "-" }}',
                    'Present'
                ]);
            @endforeach

            // Create workbook and worksheet
            const ws = XLSX.utils.aoa_to_sheet(excelData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Attendance');

            // Style the worksheet (basic styling)
            const range = XLSX.utils.decode_range(ws['!ref']);

            // Auto-width columns
            const colWidths = [];
            for (let C = range.s.c; C <= range.e.c; ++C) {
                let maxWidth = 10;
                for (let R = range.s.r; R <= range.e.r; ++R) {
                    const cellAddress = XLSX.utils.encode_cell({ r: R, c: C });
                    const cell = ws[cellAddress];
                    if (cell && cell.v) {
                        const cellLength = cell.v.toString().length;
                        maxWidth = Math.max(maxWidth, cellLength);
                    }
                }
                colWidths[C] = { wch: Math.min(maxWidth + 2, 50) };
            }
            ws['!cols'] = colWidths;

            // Save file
            const fileName = `Attendance_${courseCode}_${section}_${attendanceDate.replace(/\//g, '-')}.xlsx`;
            XLSX.writeFile(wb, fileName);

        } catch (error) {
            console.error('Error generating Excel:', error);
            alert('Error generating Excel file. Please try again.');
        } finally {
            // Hide loading modal
            $('#loadingModal').modal('hide');
        }
    }, 500);
}
</script>
@endsection
