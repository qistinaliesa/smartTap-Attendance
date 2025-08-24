@extends('master.layout')

@section('content')
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Lecturer Registration Form</h4>
            <p class="card-description">Register a new lecturer</p>

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
            <form method="POST" action="{{ route('lecturer.register') }}">
              @csrf

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Full Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control @error('name') is-invalid @enderror"
                         name="name" placeholder="Name" value="{{ old('name') }}" required>
                  @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-9">
                  <input type="email" class="form-control @error('email') is-invalid @enderror"
                         name="email" placeholder="Email" value="{{ old('email') }}" required>
                  @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Staff ID</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control @error('staff_id') is-invalid @enderror"
                         name="staff_id" placeholder="Staff ID" value="{{ old('staff_id') }}">
                  @error('staff_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Kulliyyah</label>
                <div class="col-sm-9">
                  <select class="form-control @error('kulliyyah') is-invalid @enderror" name="kulliyyah" required>
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
                  <select class="form-control @error('department') is-invalid @enderror" name="department" required>
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
                  <input type="password" class="form-control @error('password') is-invalid @enderror"
                         name="password" placeholder="Password" required>
                  @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Confirm Password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                         name="password_confirmation" placeholder="Re-enter Password" required>
                  @error('password_confirmation')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <button type="submit" class="btn btn-primary mr-2">Submit</button>
              <button type="reset" class="btn btn-light">Cancel</button>
            </form>
            <!-- Form End -->

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

