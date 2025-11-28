<header id="page-topbar">
    <div class="layout-width">
        <div class="navbar-header">
            <div class="d-flex">
                <!-- LOGO -->
                <div class="navbar-brand-box horizontal-logo">
                    <a href="index.html" class="logo logo-dark">
                        <span class="logo-sm">
                            <img src="{{url('website')}}/assets/images/logo-sm.png" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{url('website')}}/assets/images/logo-dark.png" alt="" height="17">
                        </span>
                    </a>

                    <a href="index.html" class="logo logo-light">
                        <span class="logo-sm">
                            <img src="{{url('website')}}/assets/images/logo-sm.png" alt="" height="22">
                        </span>
                        <span class="logo-lg">
                            <img src="{{url('website')}}/assets/images/logo-light.png" alt="" height="17">
                        </span>
                    </a>
                </div>

                <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger material-shadow-none" id="topnav-hamburger-icon">
                    <span class="hamburger-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </button>


            </div>

            <div class="d-flex align-items-center">

                <div class="dropdown d-md-none topbar-head-dropdown header-item">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle" id="page-header-search-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-search fs-22"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" aria-labelledby="page-header-search-dropdown">
                        <form class="p-3">
                            <div class="form-group m-0">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search ..." aria-label="Recipient's username">
                                    <button class="btn btn-primary" type="submit"><i class="mdi mdi-magnify"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>


                <div class="ms-1 header-item d-none d-sm-flex">
                    <button type="button" class="btn btn-icon btn-topbar material-shadow-none btn-ghost-secondary rounded-circle d-none" data-toggle="fullscreen" id="fullScreenMode">
                        <i class='bx bx-fullscreen fs-22'></i>
                    </button>
                </div>

                <div class="dropdown ms-sm-3 header-item topbar-user">

    @if(Auth::check())
        <!-- LOGGED-IN USER BUTTON -->
        <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown"
                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center">
                <i class="mdi mdi-account-circle text-muted fs-24 align-middle me-1"></i>
                <span class="text-start ms-xl-2">
                    <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">
                        {{ Auth::user()->employee->full_name ?? Auth::user()->username }}
                    </span>
                </span>
            </span>
        </button>

        <!-- LOGGED-IN DROPDOWN -->
        <div class="dropdown-menu dropdown-menu-end">
            <h6 class="dropdown-header">
                Welcome, {{ Auth::user()->name }}!
            </h6>

            <a class="dropdown-item" href="{{ url('users/change-password') }}">
                <i class="ri-key-2-fill text-muted fs-16 align-middle me-1"></i>
                Change Password
            </a>

            <a class="dropdown-item" href="{{ route('logout') }}"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i>
                Logout
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>

    @else
        <!-- NOT LOGGED IN -->
        <a href="{{ route('login') }}" class="btn btn-success">Login</a>
        <a href="{{ route('register') }}" class="btn btn-primary ms-2">Register</a>
    @endif

</div>



</header>
<script src="{{url('website')}}/assets/libs/jquery/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        $('#darkModeToggle').on('click', function () {
            console.log('ok');
            $.ajax({
                url: "{{ url('toggle-dark-mode') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function (response) {
                    if (response.status === 'success') {
                        if (response.value == 1) {
                            // $('#darkModeToggle i').removeClass('bx-moon').addClass('bx-sun');
                            // alert('Dark mode has been enabled.');
                        } else {
                            // $('#darkModeToggle i').removeClass('bx-sun').addClass('bx-moon');
                            // alert('Dark mode has been disabled.');
                        }
                    } else {
                        alert('Failed to toggle mode.');
                    }
                },

            });
        });

        $('#fullScreenMode').on('click', function () {
            // console.log('ok');
            $.ajax({
                url: "{{ url('fullScreen-mode') }}",
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                },
                success: function (response) {
                    if (response.status === 'success') {
                        if (response.value == 1) {
                            // $('#fullScreenMode i').removeClass('bx-moon').addClass('bx-fullscreen');
                            // alert('full Screen Mode has been enabled.');
                        } else {
                            // $('#fullScreenMode i').removeClass('bx-fullscreen').addClass('bx-moon');
                            // alert('full Screen Mode has been disabled.');
                        }
                    } else {
                        alert('Failed to toggle mode.');
                    }
                },

            });
        });
    });
</script>

