@extends('master.layout')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Courses Registration Form</h4>
            <p class="card-description">Register new courses</p>

            <!-- Success Message -->
            @if(session('success'))
              <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
            @endif

            <!-- Validation Errors -->
            @if($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <!-- Form Start -->
            {{-- <form method="POST" action="{{ route('course.store') }}">
              @csrf --}}

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Course Code</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control @error('course_code') is-invalid @enderror"
                         name="course_code" placeholder="Course Code (e.g., CS1234)"
                         value="{{ old('course_code') }}" required>
                  @error('course_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Title</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control @error('title') is-invalid @enderror"
                         name="title" placeholder="Course Title"
                         value="{{ old('title') }}" required>
                  @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Credit Hours</label>
                <div class="col-sm-9">
                  <input type="number" class="form-control @error('credit_hours') is-invalid @enderror"
                         name="credit_hours" placeholder="Credit Hours" min="1" max="6"
                         value="{{ old('credit_hours') }}" required>
                  @error('credit_hours')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Section</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control @error('section') is-invalid @enderror"
                         name="section" placeholder="Section (e.g., 01, 02)"
                         value="{{ old('section') }}" required>
                  @error('section')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Assign Lecturer</label>
                <div class="col-sm-9">
                  <select class="form-control @error('lecturer_id') is-invalid @enderror"
                          name="lecturer_id" required>
                    <option value="">Select Lecturer</option>
                    @if(isset($lecturers) && $lecturers->count() > 0)
                      @foreach($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}" {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}>
                          {{ $lecturer->name }} ({{ $lecturer->staff_id ?? 'No Staff ID' }}) - {{ $lecturer->department }}
                        </option>
                      @endforeach
                    @else
                      <option value="" disabled>No lecturers available</option>
                    @endif
                  </select>
                  @error('lecturer_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <button type="submit" class="btn btn-primary mr-2">Submit</button>
              <button type="reset" class="btn btn-light">Cancel</button>
            {{-- </form> --}}
            <!-- Form End -->

            <!-- Display All Registered Courses -->
            @if(isset($courses) && $courses->count() > 0)
            <div class="mt-5">
              <h4>All Registered Courses</h4>
              <div class="table-responsive">
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Course Code</th>
                      <th>Title</th>
                      <th>Credit Hours</th>
                      <th>Section</th>
                      <th>Assigned Lecturer</th>
                      <th>Created Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($courses as $index => $course)
                    <tr>
                      <td>{{ $index + 1 }}</td>
                      <td>{{ $course->course_code }}</td>
                      <td>{{ $course->title }}</td>
                      <td>{{ $course->credit_hours }}</td>
                      <td>{{ $course->section }}</td>
                      <td>
                        {{ $course->lecturer->name ?? 'N/A' }}
                        @if($course->lecturer && $course->lecturer->staff_id)
                          <br><small class="text-muted">({{ $course->lecturer->staff_id }})</small>
                        @endif
                      </td>
                      <td>{{ $course->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
            @endif

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
