@extends('master.layout')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Course Registration</h4>
            <p class="card-description">Register a new course below</p>

            {{-- Flash Messages --}}
            @if (session('success'))
              <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
              <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
              <div class="alert alert-danger">
                <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            {{-- Course Registration Form --}}
            <form method="POST" action="{{ route('course.store') }}">
              @csrf

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Course Code</label>
                <div class="col-sm-9">
                  <input type="text" name="course_code" class="form-control @error('course_code') is-invalid @enderror"
                         placeholder="Course Code (e.g., CS1234)" value="{{ old('course_code') }}" required>
                  @error('course_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Course Title</label>
                <div class="col-sm-9">
                  <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                         placeholder="Course Title" value="{{ old('title') }}" required>
                  @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Credit Hours</label>
                <div class="col-sm-9">
                  <input type="number" name="credit_hours" class="form-control @error('credit_hours') is-invalid @enderror"
                         placeholder="Credit Hours" min="1" max="6" value="{{ old('credit_hours') }}" required>
                  @error('credit_hours')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Section</label>
                <div class="col-sm-9">
                  <input type="text" name="section" class="form-control @error('section') is-invalid @enderror"
                         placeholder="Section (e.g., 01, 02)" value="{{ old('section') }}" required>
                  @error('section')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Assign Lecturer</label>
                <div class="col-sm-9">
                  <select name="lecturer_id" class="form-control @error('lecturer_id') is-invalid @enderror" required>
                    <option value="">Select Lecturer</option>
                    @if(isset($lecturers) && $lecturers->count() > 0)
                      @foreach($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}" {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}>
                          {{ $lecturer->name }}
                          @if($lecturer->staff_id) ({{ $lecturer->staff_id }}) @endif
                          - {{ $lecturer->department }}
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

              <button type="submit" class="btn btn-primary mr-2">Register Course</button>
              <button type="reset" class="btn btn-light">Cancel</button>
            </form>

            <hr class="my-4">
            <p>Total Courses: <strong>{{ isset($courses) ? $courses->count() : 'N/A' }}</strong></p>
          </div>
        </div>
      </div>
    </div>

    {{-- Table: All Courses --}}
    @if(isset($courses) && $courses->count())
    <div class="row mt-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">All Registered Courses</h4>
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
                    <th>Registered At</th>
                    <th>Actions</th>
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
                    <td>
                      <button class="btn btn-sm btn-outline-info edit-course"
                        data-id="{{ $course->id }}" data-toggle="modal"
                        data-target="#editCourseModal">Update</button>
                      <button class="btn btn-sm btn-outline-danger delete-course"
                        data-id="{{ $course->id }}" data-name="{{ $course->course_code }}">Delete</button>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>

  {{-- Edit Course Modal --}}
  <div class="modal fade" id="editCourseModal" tabindex="-1" aria-labelledby="editCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="editCourseForm">
          <div class="modal-header">
            <h5 class="modal-title">Edit Course</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            @csrf
            <input type="hidden" id="edit_course_id">

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Course Code</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="edit_course_code" name="course_code" required>
                <div class="invalid-feedback"></div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Course Title</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="edit_title" name="title" required>
                <div class="invalid-feedback"></div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Credit Hours</label>
              <div class="col-sm-8">
                <input type="number" class="form-control" id="edit_credit_hours" name="credit_hours" min="1" max="6" required>
                <div class="invalid-feedback"></div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Section</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="edit_section" name="section" required>
                <div class="invalid-feedback"></div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Assign Lecturer</label>
              <div class="col-sm-8">
                <select class="form-control" id="edit_lecturer_id" name="lecturer_id" required>
                  <option value="">Select Lecturer</option>
                  @if(isset($lecturers) && $lecturers->count() > 0)
                    @foreach($lecturers as $lecturer)
                      <option value="{{ $lecturer->id }}">
                        {{ $lecturer->name }}
                        @if($lecturer->staff_id) ({{ $lecturer->staff_id }}) @endif
                        - {{ $lecturer->department }}
                      </option>
                    @endforeach
                  @endif
                </select>
                <div class="invalid-feedback"></div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" id="cancelEditBtn">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Course</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Include SweetAlert and JS --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Load course data into modal
  document.querySelectorAll('.edit-course').forEach(button => {
    button.addEventListener('click', function () {
      const id = this.getAttribute('data-id');
      fetch(`/admin/courses/${id}/edit`)
        .then(response => response.json())
        .then(data => {
          document.getElementById('edit_course_id').value = data.id;
          document.getElementById('edit_course_code').value = data.course_code;
          document.getElementById('edit_title').value = data.title;
          document.getElementById('edit_credit_hours').value = data.credit_hours;
          document.getElementById('edit_section').value = data.section;
          document.getElementById('edit_lecturer_id').value = data.lecturer_id;
        });
    });
  });

  // Submit updated data
  document.getElementById('editCourseForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('edit_course_id').value;
    const form = e.target;
    const formData = new FormData(form);
    formData.append('_method', 'PUT');

    // Clear old errors
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

    fetch(`/admin/courses/${id}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      },
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          Swal.fire('Updated!', data.message, 'success').then(() => location.reload());
        } else if (data.errors) {
          Object.entries(data.errors).forEach(([key, messages]) => {
            const input = document.getElementById(`edit_${key}`);
            const feedback = input?.nextElementSibling;
            input?.classList.add('is-invalid');
            if (feedback) feedback.textContent = messages[0];
          });
        } else {
          Swal.fire('Error!', data.message || 'Something went wrong.', 'error');
        }
      })
      .catch(err => {
        console.error(err);
        Swal.fire('Error!', 'Update failed.', 'error');
      });
  });

  document.getElementById('cancelEditBtn').addEventListener('click', function () {
    $('#editCourseModal').modal('hide'); // jQuery method for Bootstrap 4
  });

  // Delete course functionality
  document.querySelectorAll('.delete-course').forEach(button => {
    button.addEventListener('click', function() {
      const courseId = this.getAttribute('data-id');
      const courseName = this.getAttribute('data-name');

      Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete course ${courseName}. This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(`/admin/courses/${courseId}`, {
            method: 'DELETE',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
              'Accept': 'application/json',
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              Swal.fire('Deleted!', data.message, 'success').then(() => {
                location.reload();
              });
            } else {
              Swal.fire('Error!', data.message, 'error');
            }
          })
          .catch(error => {
            console.error('Error:', error);
            Swal.fire('Error!', 'An error occurred while deleting the course.', 'error');
          });
        }
      });
    });
  });

  // Clear form validation on modal close
  document.getElementById('editCourseModal').addEventListener('hidden.bs.modal', function() {
    const form = document.getElementById('editCourseForm');
    form.querySelectorAll('.is-invalid').forEach(input => {
      input.classList.remove('is-invalid');
    });
    form.querySelectorAll('.invalid-feedback').forEach(feedback => {
      feedback.textContent = '';
    });
  });
});
</script>

@endsection
