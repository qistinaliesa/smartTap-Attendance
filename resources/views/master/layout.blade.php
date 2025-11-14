<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SmartTap</title>

    <!-- base:css -->
    <link rel="stylesheet" href="{{ asset('vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <!-- endinject -->

    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->

    <!-- inject:css -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- endinject -->
<meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('images/logosmart.png') }}" />
  </head>

<body>
  <div class="container-scroller d-flex">
    <!-- partial:./partials/_sidebar.html -->
    <nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #006400;">
      <ul class="nav">
        <li class="nav-item sidebar-category">
            <img src="{{ asset('images/smarttap.png') }}" alt="logo" style="max-height: 70px; margin-right: 10px;">
          <p>Admin Dashboard</p>
          {{-- <p>Navigation</p> --}}
          <span></span>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.home') }}">
            <i class="mdi mdi-view-quilt menu-icon"></i>
            <span class="menu-title">Dashboard</span>
            {{-- <div class="badge badge-info badge-pill">2</div> --}}
          </a>
        </li>
        <li class="nav-item sidebar-category">
          <p>Components</p>

          <span></span>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('lecturer.register') }}">
                <i class="mdi mdi-view-headline menu-icon"></i>
                <span class="menu-title">Lecturer Registration</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('cards.index') }}">
                <i class="mdi mdi-view-headline menu-icon"></i>
                <span class="menu-title">Student Registration</span>
            </a>
        </li>
         <li class="nav-item">
            <a class="nav-link" href="{{ route('course.register') }}">
                <i class="mdi mdi-view-headline menu-icon"></i>
                <span class="menu-title">Course Registration</span>
            </a>
        </li>

        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link btn btn-link">
                    <i class="mdi mdi-logout menu-icon"></i>
                    <span class="menu-title">Logout</span>
                </button>
            </form>
        </li>



      </ul>
    </nav>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:./partials/_navbar.html -->
      <nav class="navbar col-lg-12 col-12 px-0 py-1 d-flex flex-row" style="height: 140px;">


        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">

          <div class="navbar-brand-wrapper">
            <a class="navbar-brand brand-logo" href="index.html"><img src="{{ asset('images/smarttap.png') }}" alt="logo" style="max-height: 70px;">
                <h4 class="font-weight-bold mb-0 d-none d-md-block mt-1 text-white">Welcome back !</h4>

            </a>
            <a class="navbar-brand brand-logo-mini" href="index.html"><img src="images/smarttap.png" alt="logo"/></a>
          </div>

          <ul class="navbar-nav navbar-nav-right">

      </nav>


      <div class="d-flex flex-column min-vh-100">

        <main class="flex-grow-1">
          @yield('content')
        </main>



      </div>

      <!-- partial -->
    </div>
    <!-- main-panel ends -->
  </div>
  <!-- page-body-wrapper ends -->
</div>
<!-- container-scroller -->

<!-- base:js -->
<script src="vendors/js/vendor.bundle.base.js"></script>
<!-- endinject -->
<!-- Plugin js for this page-->
<script src="vendors/chart.js/Chart.min.js"></script>
<!-- End plugin js for this page-->
<!-- inject:js -->
<script src="js/off-canvas.js"></script>
<script src="js/hoverable-collapse.js"></script>
<script src="js/template.js"></script>
<!-- endinject -->
<!-- plugin js for this page -->
<!-- End plugin js for this page -->
<!-- Custom js for this page-->
<script src="js/dashboard.js"></script>
<!-- End custom js for this page-->
<script src="{{ asset('js/chart.js') }}"></script>
<script src="{{ asset('vendors/chart.js/Chart.min.js') }}"></script>
<script src="../../js/file-upload.js"></script>
<!-- Bootstrap JS (required for modal dismiss) -->

</body>

</html>
