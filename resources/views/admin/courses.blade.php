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

            <!-- Form Start -->


              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Course Code</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="name" placeholder="Name" required>
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Title</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="Title" placeholder="Title" required>
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Credit Hours</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="Credit Hours" placeholder="Credit Hours">
                </div>
              </div>

              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Section</SEct></label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="Section" placeholder="Section">
                </div>
              </div>


              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Assign Lecturer</label>
                <div class="col-sm-9">
                  <select class="form-control" name="department">
                    <option></option>
                    <option></option>
                    <option></option>
                  </select>
                </div>
              </div>

              <button type="submit" class="btn btn-primary mr-2">Submit</button>
              <button type="reset" class="btn btn-light">Cancel</button>

            <!-- Form End -->

          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
