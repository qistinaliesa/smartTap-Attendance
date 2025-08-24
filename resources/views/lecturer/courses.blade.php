@extends('master.userlayout')

@section('content')
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Classes</h4>

                        @if($courses->count() > 0)
                            <div class="row">
                                @foreach($courses as $index => $course)
                                    <div class="col-md-4 mb-4">
                                        {{-- Make the entire card clickable --}}
                                        <a href="{{ route('lecturer.course.show', $course->id) }}" class="course-card-link">
                                            <div class="course-card course-card-{{ $index % 6 + 1 }}">
                                                <div class="course-content">
                                                    <h5 class="course-code">{{ $course->course_code }}</h5>
                                                    <p class="course-section">SEC {{ $course->section }}</p>
                                                    <small class="credit-hours">{{ $course->credit_hours }} Credit Hours</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="mdi mdi-book-open-variant" style="font-size: 4rem; color: #ccc;"></i>
                                </div>
                                <h5 class="text-muted">No Courses Assigned</h5>
                                <p class="text-muted">You don't have any courses assigned yet. Please contact the administrator.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.course-card-link {
    text-decoration: none;
    color: inherit;
}

.course-card-link:hover {
    text-decoration: none;
    color: inherit;
}

.course-card {
    background: linear-gradient(135deg, #e3f2fd, #bbdefb);
    border-radius: 20px;
    padding: 30px 20px;
    text-align: center;
    height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.course-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

/* Light Blue */
.course-card-1 {
    background: #B8E6E6 !important;
}

/* Light Yellow */
.course-card-2 {
    background: #F5E6A3 !important;
}

/* Light Pink */
.course-card-3 {
    background: #F4C2C2 !important;
}

/* Light Purple */
.course-card-4 {
    background: #D1B3E6 !important;
}

/* Light Green */
.course-card-5 {
    background: #A8D8A8 !important;
}

/* Light Teal/Blue */
.course-card-6 {
    background: #A3D5E6 !important;
}

.course-content {
    width: 100%;
}

.course-code {
    font-size: 18px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 8px;
    line-height: 1.2;
}

.course-section {
    font-size: 14px;
    color: #34495e;
    margin-bottom: 5px;
    font-weight: 500;
}

.credit-hours {
    font-size: 12px;
    color: #7f8c8d;
    display: block;
}
</style>
@endsection
