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

    <style>
        /* Fix sidebar gap issue */
        .container-scroller {
            display: flex !important;
        }

        .sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            z-index: 1000 !important;
        }

        .container-fluid.page-body-wrapper {
            margin-left: 230px !important;
            padding-left: 0 !important;
        }

        /* Remove any gaps or spacing */
        .sidebar, .page-body-wrapper {
            margin: 0 !important;
        }

        /* Ensure no body margins */
        body {
            margin: 0 !important;
            padding: 0 !important;
        }
    </style>
  </head>

<body>
  <div class="container-scroller d-flex">
    <!-- partial:./partials/_sidebar.html -->
    <nav class="sidebar sidebar-offcanvas" id="sidebar" style="background-color: #006400;">
      <ul class="nav">
        <li class="nav-item sidebar-category">
            <img src="{{ asset('images/smarttap.png') }}" alt="logo" style="max-height: 70px; margin-right: 10px;">
          <p>User Dashboard</p>
          {{-- <p>Navigation</p> --}}
          <span></span>
        </li>
        {{-- <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.home') }}">
            <i class="mdi mdi-view-quilt menu-icon"></i>
            <span class="menu-title">Dashboard</span>
            <div class="badge badge-info badge-pill">2</div>
          </a>
        </li> --}}
    <li class="nav-item sidebar-category">
          <p>Navigation</p>
          <span></span>
        </li>

        <!-- Simple direct link to Classes - NO DROPDOWN -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('lecturer.courses') }}">
                <i class="mdi mdi-school menu-icon"></i>
                <span class="menu-title">My Classes</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('attendance.index') }}">
                <i class="mdi mdi-view-headline menu-icon"></i>
                <span class="menu-title">Attendance</span>
            </a>
         </li>
         <li class="nav-item">
            <a class="nav-link" href="{{ route('lecturer.change_password.form') }}">
                <i class="mdi mdi-lock menu-icon"></i>
                <span class="menu-title">Change Password</span>
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

        {{-- <li class="nav-item sidebar-category">
          <p>Pages</p>
          <span></span>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
            <i class="mdi mdi-account menu-icon"></i>
            <span class="menu-title">Other</span>
            <i class="menu-arrow"></i>
          </a>
          <div class="collapse" id="auth">
            <ul class="nav flex-column sub-menu">
              <li class="nav-item"> <a class="nav-link" href="pages/samples/login.html"> Login </a></li>
              <li class="nav-item"> <a class="nav-link" href="pages/samples/login-2.html"> Login 2 </a></li>
              <li class="nav-item"> <a class="nav-link" href="pages/samples/register.html"> Register </a></li>
              <li class="nav-item"> <a class="nav-link" href="pages/samples/register-2.html"> Register 2 </a></li>
              <li class="nav-item"> <a class="nav-link" href="pages/samples/lock-screen.html"> Lockscreen </a></li>
            </ul>
          </div>
        </li> --}}

      </ul>
    </nav>
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:./partials/_navbar.html -->
      <nav class="navbar col-lg-12 col-12 px-0 py-1 d-flex flex-row" style="height: 140px;">


        <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
          {{-- <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
            <span class="mdi mdi-menu"></span>
          </button> --}}
          <div class="navbar-brand-wrapper">
            <a class="navbar-brand brand-logo" href="index.html"><img src="{{ asset('images/smarttap.png') }}" alt="logo" style="max-height: 70px;">
                {{-- <h4 class="font-weight-bold mb-0 d-none d-md-block mt-1 text-white">Welcome back, Ts. Dr. Aidrina</h4> --}}
                <h4 class="font-weight-bold mb-0 d-none d-md-block mt-1 text-white">
                    Welcome back,
                    @if(Auth::guard('lecturer')->check())
                        {{ Auth::guard('lecturer')->user()->name }}
                    @elseif(Auth::check())
                        {{ Auth::user()->name }}
                    @else
                        Guest
                    @endif
                </h4>

            </a>
            <a class="navbar-brand brand-logo-mini" href="index.html"><img src="images/smarttap.png" alt="logo"/></a>
          </div>

          <ul class="navbar-nav navbar-nav-right">
            {{-- <li class="nav-item">
              <h4 class="mb-0 font-weight-bold d-none d-xl-block">Mar 12, 2025 - Apr 10, 2025</h4>
            </li> --}}
            <li class="nav-item dropdown mr-1">
              <a class="nav-link count-indicator dropdown-toggle d-flex justify-content-center align-items-center" id="messageDropdown" href="#" data-toggle="dropdown">
                <i class="mdi mdi-calendar mx-0"></i>
                <span class="count bg-info">2</span>
              </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="messageDropdown">
                <p class="mb-0 font-weight-normal float-left dropdown-header">Messages</p>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                      <img src="images/faces/face4.jpg" alt="image" class="profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow">
                    <h6 class="preview-subject ellipsis font-weight-normal">David Grey
                    </h6>
                    <p class="font-weight-light small-text text-muted mb-0">
                      The meeting is cancelled
                    </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                      <img src="images/faces/face2.jpg" alt="image" class="profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow">
                    <h6 class="preview-subject ellipsis font-weight-normal">Tim Cook
                    </h6>
                    <p class="font-weight-light small-text text-muted mb-0">
                      New product launch
                    </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                      <img src="images/faces/face3.jpg" alt="image" class="profile-pic">
                  </div>
                  <div class="preview-item-content flex-grow">
                    <h6 class="preview-subject ellipsis font-weight-normal"> Johnson
                    </h6>
                    <p class="font-weight-light small-text text-muted mb-0">
                      Upcoming board meeting
                    </p>
                  </div>
                </a>
              </div>
            </li>
            <li class="nav-item dropdown mr-2">
              <a class="nav-link count-indicator dropdown-toggle d-flex align-items-center justify-content-center" id="notificationDropdown" href="#" data-toggle="dropdown">
                <i class="mdi mdi-email-open mx-0"></i>
                <span class="count bg-danger">1</span>
              </a>
              <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-success">
                      <i class="mdi mdi-information mx-0"></i>
                    </div>
                  </div>
                  <div class="preview-item-content">
                    <h6 class="preview-subject font-weight-normal">Application Error</h6>
                    <p class="font-weight-light small-text mb-0 text-muted">
                      Just now
                    </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-warning">
                      <i class="mdi mdi-settings mx-0"></i>
                    </div>
                  </div>
                  <div class="preview-item-content">
                    <h6 class="preview-subject font-weight-normal">Settings</h6>
                    <p class="font-weight-light small-text mb-0 text-muted">
                      Private message
                    </p>
                  </div>
                </a>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-info">
                      <i class="mdi mdi-account-box mx-0"></i>
                    </div>
                  </div>
                  <div class="preview-item-content">
                    <h6 class="preview-subject font-weight-normal">New user registration</h6>
                    <p class="font-weight-light small-text mb-0 text-muted">
                      2 days ago
                    </p>
                  </div>
                </a>
              </div>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>

      </nav>


      <div class="d-flex flex-column min-vh-100">

        <main class="flex-grow-1">
          @yield('content')
        </main>

        <footer class="footer mt-auto">
          <div class="card">


          </div>
        </footer>

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
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Before closing </body> -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
