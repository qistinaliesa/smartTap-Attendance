<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RFID Attendance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">RFID Attendance System</div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('cards.index') }}" class="btn btn-primary">View Registered Cards</a>
                            <a href="{{ route('attendance.index') }}" class="btn btn-success">View Attendance Records</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
