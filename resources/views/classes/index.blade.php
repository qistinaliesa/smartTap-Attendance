@extends('master.userlayout')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 mb-4">
        <h2 class="font-weight-bold text-primary">My Classes</h2>
        <p class="text-muted">View all your registered courses</p>
      </div>
    </div>

    @if($courses->count() > 0)
      <div class="row">
        @foreach($courses as $course)
          <div class="col-lg-4 col-md-6 mb-4">
            <div class="card course-card h-100" style="border-radius: 15px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); transition: transform 0.2s;">
              @php
                $colors = [
                  'background: linear-gradient(135deg, #E3F2FD, #BBDEFB);', // Blue
                  'background: linear-gradient(135deg, #FFF3E0, #FFE0B2);', // Orange
                  'background: linear-gradient(135deg, #FCE4EC, #F8BBD9);', // Pink
                  'background: linear-gradient(135deg, #F3E5F5, #E1BEE7);', // Purple
                  'background: linear-gradient(135deg, #E8F5E8, #C8E6C9);', // Green
                  'background: linear-gradient(135deg, #E0F2F1, #B2DFDB);', // Teal
                ];
                $colorStyle = $colors[$loop->index % 6];
              @endphp
              <div class="card-body d-flex flex-column justify-content-between text-center p-4"
                   style="{{ $colorStyle }} border-radius: 15px;">

                <div>
                  <h4 class="card-title font-weight-bold text-dark mb-3">{{ $course->course_code }}</h4>
                  <h6 class="text-dark mb-2">{{ $course->title }}</h6>
                  <p class="text-muted mb-1">
                    <i class="mdi mdi-account-circle"></i>
                    {{ $course->lecturer->name ?? 'No Lecturer Assigned' }}
                  </p>
                  @if($course->lecturer && $course->lecturer->staff_id)
                    <p class="text-muted mb-1">
                      <i class="mdi mdi-card-account-details"></i>
                      {{ $course->lecturer->staff_id }}
                    </p>
                  @endif
                </div>

                <div class="mt-3">
                  <div class="row text-center">
                    <div class="col-6">
                      <p class="mb-1 text-muted small">Credit Hours</p>
                      <span class="badge badge-info px-3 py-2">{{ $course->credit_hours }}</span>
                    </div>
                    <div class="col-6">
                      <p class="mb-1 text-muted small">Section</p>
                      <span class="badge badge-warning px-3 py-2">{{ $course->section }}</span>
                    </div>
                  </div>

                  <div class="mt-3">
                    <button class="btn btn-primary btn-sm px-4" onclick="viewCourseDetails('{{ $course->id }}')">
                      <i class="mdi mdi-eye"></i> View Details
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>

      <!-- Course Statistics -->
      <div class="row mt-4">
        <div class="col-md-3">
          <div class="card bg-gradient-info text-white">
            <div class="card-body text-center">
              <i class="mdi mdi-book-open-page-variant mdi-24px mb-2"></i>
              <h3 class="font-weight-bold">{{ $courses->count() }}</h3>
              <p class="mb-0">Total Courses</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-gradient-success text-white">
            <div class="card-body text-center">
              <i class="mdi mdi-clock mdi-24px mb-2"></i>
              <h3 class="font-weight-bold">{{ $courses->sum('credit_hours') }}</h3>
              <p class="mb-0">Total Credit Hours</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-gradient-warning text-white">
            <div class="card-body text-center">
              <i class="mdi mdi-account-multiple mdi-24px mb-2"></i>
              <h3 class="font-weight-bold">{{ $courses->groupBy('lecturer_id')->count() }}</h3>
              <p class="mb-0">Different Lecturers</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card bg-gradient-primary text-white">
            <div class="card-body text-center">
              <i class="mdi mdi-format-list-numbered mdi-24px mb-2"></i>
              <h3 class="font-weight-bold">{{ $courses->groupBy('section')->count() }}</h3>
              <p class="mb-0">Different Sections</p>
            </div>
          </div>
        </div>
      </div>
    @else
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-body text-center py-5">
              <i class="mdi mdi-book-open-page-variant mdi-48px text-muted mb-3"></i>
              <h4 class="text-muted">No Classes Found</h4>
              <p class="text-muted">You don't have any registered courses yet.</p>
              @if(Auth::user()->utype === 'admin')
                <a href="{{ route('course.register') }}" class="btn btn-primary">
                  <i class="mdi mdi-plus"></i> Register New Course
                </a>
              @endif
            </div>
          </div>
        </div>
      </div>
    @endif
  </div>
</div>

<!-- Course Details Modal -->
<div class="modal fade" id="courseDetailsModal" tabindex="-1" aria-labelledby="courseDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="courseDetailsModalLabel">Course Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="courseDetailsBody">
        <!-- Course details will be loaded here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<style>
.course-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 16px rgba(0,0,0,0.2) !important;
}

.card {
  border: none;
}

.badge {
  font-size: 0.75rem;
  font-weight: 500;
}
</style>

<script>
function viewCourseDetails(courseId) {
  // You can fetch course details via AJAX or show a modal with course information
  fetch(`/admin/courses/${courseId}`)
    .then(response => response.text())
    .then(html => {
      document.getElementById('courseDetailsBody').innerHTML = html;
      $('#courseDetailsModal').modal('show');
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Failed to load course details');
    });
}
</script>

@endsection
