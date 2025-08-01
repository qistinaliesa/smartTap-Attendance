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

            <!-- Form Start -->
            <form class="forms-sample" method="POST" action="{{ route('admin.lecturer.store') }}">
              @csrf

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Full Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="name" placeholder="Name" required>
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-9">
                  <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Staff ID</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="staff_id" placeholder="Staff ID">
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Kulliyyah</label>
                <div class="col-sm-9">
                  <select class="form-control" name="kulliyyah">
                    <option>KICT</option>
                    <option>ECONS</option>
                    <option>AIKOL</option>
                  </select>
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Department</label>
                <div class="col-sm-9">
                  <select class="form-control" name="department">
                    <option>BIT</option>
                    <option>BCS</option>
                    <option>AIKOL</option>
                  </select>
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Confirm Password</label>
                <div class="col-sm-9">
                  <input type="password" class="form-control" name="password_confirmation" placeholder="Re-enter Password" required>
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

