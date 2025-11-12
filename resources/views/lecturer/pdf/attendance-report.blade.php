<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report - {{ $course->course_code }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .header h2 {
            font-size: 14px;
            margin-bottom: 3px;
            color: #34495e;
        }

        .header p {
            font-size: 9px;
            color: #7f8c8d;
            margin-top: 5px;
        }

        .course-info {
            margin-bottom: 15px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }

        .course-info table {
            width: 100%;
        }

        .course-info td {
            padding: 3px 0;
        }

        .course-info strong {
            color: #2c3e50;
        }

        @if($include_summary)
        .summary {
            margin-bottom: 15px;
            background: #e8f5e9;
            padding: 10px;
            border-left: 4px solid #4caf50;
        }

        .summary h3 {
            font-size: 12px;
            margin-bottom: 8px;
            color: #2e7d32;
        }

        .summary-grid {
            display: table;
            width: 100%;
        }

        .summary-item {
            display: table-cell;
            width: 33%;
            padding: 5px;
            text-align: center;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #2e7d32;
            display: block;
        }

        .summary-label {
            font-size: 9px;
            color: #666;
            display: block;
            margin-top: 2px;
        }
        @endif

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .attendance-table th,
        .attendance-table td {
            border: 1px solid #ddd;
            padding: 6px 4px;
            text-align: center;
        }

        .attendance-table th {
            background-color: #34495e;
            color: white;
            font-weight: bold;
            font-size: 9px;
        }

        .attendance-table td {
            font-size: 9px;
        }

        .attendance-table .student-info {
            text-align: left;
            padding-left: 8px;
        }

        .attendance-table .student-name {
            font-weight: bold;
            color: #2c3e50;
        }

        .attendance-table .matric-id {
            color: #7f8c8d;
            font-size: 8px;
        }

        .present {
            background-color: #c8e6c9;
            color: #2e7d32;
            font-weight: bold;
        }

        .absent {
            background-color: #ffcdd2;
            color: #c62828;
            font-weight: bold;
        }

        .status-good {
            background-color: #e8f5e9;
        }

        .status-cautious {
            background-color: #fff9c4;
        }

        .status-critical {
            background-color: #ffebee;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #7f8c8d;
            text-align: center;
        }

        .legend {
            margin-bottom: 15px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .legend-title {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .legend-items {
            display: table;
            width: 100%;
        }

        .legend-item {
            display: table-cell;
            padding: 3px 8px;
            font-size: 8px;
        }

        .legend-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            margin-right: 5px;
            border: 1px solid #ccc;
            vertical-align: middle;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ATTENDANCE REPORT</h1>
        <h2>{{ $course->course_code }} - {{ $course->course_name ?? 'Course Name' }}</h2>
        <p>Section {{ $course->section }} | {{ $course->credit_hours }} Credit Hours</p>
        <p>Lecturer: {{ $lecturer->name }} | Generated: {{ $generated_at }}</p>
    </div>

    <div class="course-info">
        <table>
            <tr>
                <td width="33%"><strong>Course Code:</strong> {{ $course->course_code }}</td>
                <td width="33%"><strong>Section:</strong> {{ $course->section }}</td>
                <td width="34%"><strong>Report Period:</strong> {{ $summary['date_range'] }}</td>
            </tr>
        </table>
    </div>

    @if($include_summary)
    <div class="summary">
        <h3>📊 Attendance Summary</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-value">{{ $summary['total_students'] }}</span>
                <span class="summary-label">Total Students</span>
            </div>
            <div class="summary-item">
                <span class="summary-value">{{ $summary['total_classes'] }}</span>
                <span class="summary-label">Classes Included</span>
            </div>
            <div class="summary-item">
                <span class="summary-value">{{ $summary['average_attendance'] }}%</span>
                <span class="summary-label">Average Attendance</span>
            </div>
        </div>
    </div>
    @endif

    <div class="legend">
        <div class="legend-title">Legend:</div>
        <div class="legend-items">
            <div class="legend-item">
                <span class="legend-box present"></span> Present (P)
            </div>
            <div class="legend-item">
                <span class="legend-box absent"></span> Absent (A)
            </div>
            <div class="legend-item">
                <span class="legend-box status-good"></span> Good (≥75%)
            </div>
            <div class="legend-item">
                <span class="legend-box status-cautious"></span> Cautious (50-74%)
            </div>
            <div class="legend-item">
                <span class="legend-box status-critical"></span> Critical (<50%)
            </div>
        </div>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="20%">Student Name</th>
                <th width="12%">Matric ID</th>
                @foreach($dates as $date)
                    <th width="{{ 40 / count($dates) }}%">{{ $date }}</th>
                @endforeach
                <th width="8%">Present</th>
                <th width="8%">Absent</th>
                <th width="7%">Rate</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                @php
                    $rowClass = '';
                    if ($student['attendance_percentage'] >= 75) {
                        $rowClass = 'status-good';
                    } elseif ($student['attendance_percentage'] >= 50) {
                        $rowClass = 'status-cautious';
                    } else {
                        $rowClass = 'status-critical';
                    }
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>{{ $student['no'] }}</td>
                    <td class="student-info">
                        <div class="student-name">{{ $student['name'] }}</div>
                    </td>
                    <td>{{ $student['matric_id'] }}</td>
                    @foreach($full_dates as $date)
                        <td class="{{ $student['attendance'][$date] ?? false ? 'present' : 'absent' }}">
                            {{ $student['attendance'][$date] ?? false ? 'P' : 'A' }}
                        </td>
                    @endforeach
                    <td><strong>{{ $student['present_count'] }}</strong></td>
                    <td><strong>{{ $student['absent_count'] }}</strong></td>
                    <td><strong>{{ $student['attendance_percentage'] }}%</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated report from SmartTap Attendance System</p>
        <p>Report generated on {{ $generated_at }} | {{ $course->course_code }} - {{ $lecturer->name }}</p>
    </div>
</body>
</html>
