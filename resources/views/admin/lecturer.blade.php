@extends('master.layout')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Lecturer Registration</h4>
            <p class="card-description">Register a new lecturer below</p>

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

            {{-- Lecturer Registration Form --}}
            <form method="POST" action="{{ route('admin.lecturer.store') }}">
              @csrf

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Full Name</label>
                <div class="col-sm-9">
                  <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                         value="{{ old('name') }}" required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Email Address</label>
                <div class="col-sm-9">
                  <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                         value="{{ old('email') }}" required>
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Staff ID</label>
                <div class="col-sm-9">
                  <input type="text" name="staff_id" class="form-control @error('staff_id') is-invalid @enderror"
                         value="{{ old('staff_id') }}">
                  @error('staff_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Kulliyyah</label>
                <div class="col-sm-9">
                  <select name="kulliyyah" class="form-control @error('kulliyyah') is-invalid @enderror" required>
                    <option value="">Select Kulliyyah</option>
                    <option value="KICT" {{ old('kulliyyah') == 'KICT' ? 'selected' : '' }}>KICT</option>
                    <option value="ECONS" {{ old('kulliyyah') == 'ECONS' ? 'selected' : '' }}>ECONS</option>
                    <option value="AIKOL" {{ old('kulliyyah') == 'AIKOL' ? 'selected' : '' }}>AIKOL</option>
                  </select>
                  @error('kulliyyah')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Department</label>
                <div class="col-sm-9">
                  <select name="department" class="form-control @error('department') is-invalid @enderror" required>
                    <option value="">Select Department</option>
                    <option value="BIT" {{ old('department') == 'BIT' ? 'selected' : '' }}>BIT</option>
                    <option value="BCS" {{ old('department') == 'BCS' ? 'selected' : '' }}>BCS</option>
                    <option value="AIKOL" {{ old('department') == 'AIKOL' ? 'selected' : '' }}>AIKOL</option>
                  </select>
                  @error('department')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Password</label>
                <div class="col-sm-9">
                  <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
                         required>
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Confirm Password</label>
                <div class="col-sm-9">
                  <input type="password" name="password_confirmation" class="form-control" required>
                </div>
              </div>

              <button type="submit" class="btn btn-primary mr-2">Register Lecturer</button>
              <button type="reset" class="btn btn-light">Cancel</button>
            </form>

            <hr class="my-4">
            <p>Total Lecturers: <strong>{{ isset($lecturers) ? $lecturers->count() : 'N/A' }}</strong></p>
          </div>
        </div>
      </div>
    </div>
 {{-- Table: All Lecturers --}}
    @if(isset($lecturers) && $lecturers->count())
    <div class="row mt-4">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">All Registered Lecturers</h4>
            <div class="table-responsive">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Staff ID</th>
                    <th>Kulliyyah</th>
                    <th>Department</th>
                    <th>Registered At</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($lecturers as $index => $lecturer)
                  <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $lecturer->name }}</td>
                    <td>{{ $lecturer->email }}</td>
                    <td>{{ $lecturer->staff_id ?? 'N/A' }}</td>
                    <td>{{ $lecturer->kulliyyah }}</td>
                    <td>{{ $lecturer->department }}</td>
                    <td>{{ $lecturer->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                      <button class="btn btn-sm btn-outline-info edit-lecturer"
                        data-id="{{ $lecturer->id }}" data-toggle="modal"
                        data-target="#editLecturerModal">Update</button>
                      <button class="btn btn-sm btn-outline-danger delete-lecturer"
                        data-id="{{ $lecturer->id }}" data-name="{{ $lecturer->name }}">Delete</button>
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

  {{-- Edit Lecturer Modal --}}
  <div class="modal fade" id="editLecturerModal" tabindex="-1" aria-labelledby="editLecturerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <form id="editLecturerForm">
          <div class="modal-header">
            <h5 class="modal-title">Edit Lecturer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            @csrf
            <input type="hidden" id="edit_lecturer_id">

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Full Name</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="edit_name" name="name" required>
                <div class="invalid-feedback"></div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Email</label>
              <div class="col-sm-8">
                <input type="email" class="form-control" id="edit_email" name="email" required>
                <div class="invalid-feedback"></div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Staff ID</label>
              <div class="col-sm-8">
                <input type="text" class="form-control" id="edit_staff_id" name="staff_id">
                <div class="invalid-feedback"></div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Kulliyyah</label>
              <div class="col-sm-8">
                <select class="form-control" id="edit_kulliyyah" name="kulliyyah" required>
                  <option value="">Select Kulliyyah</option>
                  <option value="KICT">KICT</option>
                  <option value="ECONS">ECONS</option>
                  <option value="AIKOL">AIKOL</option>
                </select>
                <div class="invalid-feedback"></div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Department</label>
              <div class="col-sm-8">
                <select class="form-control" id="edit_department" name="department" required>
                  <option value="">Select Department</option>
                  <option value="BIT">BIT</option>
                  <option value="BCS">BCS</option>
                  <option value="AIKOL">AIKOL</option>
                </select>
                <div class="invalid-feedback"></div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Password (optional)</label>
              <div class="col-sm-8">
                <input type="password" class="form-control" id="edit_password" name="password">
                <div class="invalid-feedback"></div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-4 col-form-label">Confirm Password</label>
              <div class="col-sm-8">
                <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                <div class="invalid-feedback"></div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Lecturer</button>
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
  // Load lecturer data into modal
  document.querySelectorAll('.edit-lecturer').forEach(button => {
    button.addEventListener('click', function () {
      const id = this.getAttribute('data-id');
      fetch(`/admin/lecturers/${id}/edit`)
        .then(response => response.json())
        .then(data => {
          document.getElementById('edit_lecturer_id').value = data.id;
          document.getElementById('edit_name').value = data.name;
          document.getElementById('edit_email').value = data.email;
          document.getElementById('edit_staff_id').value = data.staff_id || '';
          document.getElementById('edit_kulliyyah').value = data.kulliyyah;
          document.getElementById('edit_department').value = data.department;
        });
    });
  });

  // Submit updated data
  document.getElementById('editLecturerForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const id = document.getElementById('edit_lecturer_id').value;
    const form = e.target;
    const formData = new FormData(form);
    formData.append('_method', 'PUT');

    // Clear old errors
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

    fetch(`/admin/lecturers/${id}`, {
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

    // Delete lecturer functionality
    document.querySelectorAll('.delete-lecturer').forEach(button => {
        button.addEventListener('click', function() {
            const lecturerId = this.getAttribute('data-id');
            const lecturerName = this.getAttribute('data-name');

            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to delete ${lecturerName}. This action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/lecturers/${lecturerId}`, {
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
                        Swal.fire('Error!', 'An error occurred while deleting the lecturer.', 'error');
                    });
                }
            });
        });
    });

    // Clear form validation on modal close
    document.getElementById('editLecturerModal').addEventListener('hidden.bs.modal', function() {
        const form = document.getElementById('editLecturerForm');
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

