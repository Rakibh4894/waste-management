<div class="app-menu navbar-menu">
    <div class="navbar-brand-box">
        <a href="{{route('dashboard')}}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{url('website')}}/assets/images/logo.jpg" alt="logo" height="70">
            </span>
            <span class="logo-lg">
                <img src="{{url('website')}}/assets/images/logo.jpg" alt="logo" height="70">
            </span>
        </a>
        <a href="{{route('dashboard')}}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{url('website')}}/assets/images/logo.jpg" alt="logo" height="70">
            </span>
            <span class="logo-lg">
                <img src="{{url('website')}}/assets/images/logo.jpg" alt="logo" height="70">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
                id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>
    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{route('dashboard')}}" role="button" aria-expanded="false"
                       aria-controls="sidebarDashboards">
                        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboards</span>
                    </a>
                </li>
                <!-- end Dashboard Menu -->
                @if(Request::is('permissions/*')||Request::is('roles/*')||Request::is('users/*'))
                    @php($roleNav = true)
                @endif

{{--                @if(auth()->user()->id == 1)--}}
                @canany(['MANAGE_USERS', '000250','000254','000255','000258','000259','000262','000263'])
                <li class="nav-item">
                    <a class="nav-link menu-link {{ isset($roleNav)?'active':'' }}" href="#sidebarRole"
                       data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarRole">
                        <i class="ri-user-2-fill"></i> <span data-key="t-apps">User Role</span>
                    </a>
                    <div class="collapse menu-dropdown {{ isset($roleNav)?'show':'' }}" id="sidebarRole">
                        <ul class="nav nav-sm flex-column">
                            @canany(['000251', '000250'])
                                <li class="nav-item">
                                    <a href="#sidebarCalendar"
                                       class="nav-link {{ Request::is('roles/admin')||Request::is('roles/permission-assign/*')?'active':'' }}"
                                       data-bs-toggle="collapse" role="button" aria-expanded="false"
                                       aria-controls="sidebarCalendar" data-key="t-calender">
                                        Role
                                    </a>
                                    <div
                                        class="collapse menu-dropdown {{ Request::is('roles/admin')||Request::is('roles/permission-assign/*')?'show':'' }}"
                                        id="sidebarCalendar">
                                        <ul class="nav nav-sm flex-column">
                                            <li class="nav-item">
                                                <a href="{{url('roles/admin')}}"
                                                   class="nav-link {{ Request::is('roles/admin')||Request::is('roles/permission-assign/*')?'active':'' }}"
                                                   data-key="t-main-calender"> Admin </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            @endcanany
                            @canany(['000254', '000254'])
                                <li class="nav-item">
                                    <a href="#sidebarEmail"
                                       class="nav-link {{ Request::is('permissions/admin')?'active':'' }}"
                                       data-bs-toggle="collapse" role="button" aria-expanded="false"
                                       aria-controls="sidebarEmail" data-key="t-email">
                                        Permission
                                    </a>
                                    <div class="collapse menu-dropdown {{ Request::is('permissions/admin')?'show':'' }}"
                                         id="sidebarEmail">
                                        <ul class="nav nav-sm flex-column">
                                            <li class="nav-item">
                                                <a href="{{url('permissions/admin')}}"
                                                   class="nav-link {{ Request::is('permissions/admin')?'active':'' }}"
                                                   data-key="t-mailbox"> Admin </a>
                                            </li>

                                        </ul>
                                    </div>
                                </li>
                            @endcanany
                            @canany(['MANAGE_USERS', '000259','000262','000263'])
                                <li class="nav-item">
                                    <a href="#sidebarEcommerce"
                                       class="nav-link {{ (Request::is('users/manage-users')||Request::is('users/manage-users-permission')||Request::is('users/assign-revoke-permission/*'))?'active':'' }}"
                                       data-bs-toggle="collapse" role="button" aria-expanded="false"
                                       aria-controls="sidebarEcommerce" data-key="t-ecommerce">
                                        Users
                                    </a>
                                    <div
                                        class="collapse menu-dropdown {{ (Request::is('users/manage-users')||Request::is('users/manage-users-permission')||Request::is('users/assign-revoke-permission/*'))?'show':'' }}"
                                        id="sidebarEcommerce">
                                        <ul class="nav nav-sm flex-column">
                                            @canany(['MANAGE_USERS','000259'])
                                                <li class="nav-item">
                                                    <a href="{{url('users/manage-users')}}"
                                                       class="nav-link {{ Request::is('users/manage-users')?'active':'' }}"
                                                       data-key="t-products"> Manage Users </a>
                                                </li>
                                            @endcanany
                                            @canany(['000000','000263'])
                                                <li class="nav-item">
                                                    <a href="{{url('users/manage-users-permission')}}"
                                                       class="nav-link {{ (Request::is('users/manage-users-permission')||Request::is('users/assign-revoke-permission/*'))?'active':'' }}"
                                                       data-key="t-products"> Manage Permission </a>
                                                </li>
                                            @endcanany
                                        </ul>
                                    </div>
                                </li>
                            @endcanany
                        </ul>
                    </div>
                </li>
                @endcanany
                @canany(['000242', '000243','000246','000247'])
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('lookup/admin')||Request::is('fileUpload/admin')?'active':'' }}"
                       href="#sidebarLayouts"
                       data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarLayouts">
                        <i class="ri-layout-3-line"></i> <span data-key="t-layouts">Additional</span>
                    </a>
                    <div class="collapse menu-dropdown {{ Request::is('wards')?'show':'' }}"
                         id="sidebarLayouts">
                        <ul class="nav nav-sm flex-column">
                            @canany(['000242', '000243'])
                                <li class="nav-item">
                                    <a href="{{url('city-corporations')}}"
                                       class="nav-link {{ Request::is('city-corporations')?'active':'' }}"
                                       data-key="t-horizontal">City Corporations</a>
                                </li>
                            @endcanany
                            @canany(['000242', '000243'])
                                <li class="nav-item">
                                    <a href="{{url('wards')}}"
                                       class="nav-link {{ Request::is('wards')?'active':'' }}"
                                       data-key="t-horizontal">Wards</a>
                                </li>
                            @endcanany

                            @canany(['000242', '000243'])
                                <li class="nav-item">
                                    <a href="{{url('waste-requests')}}"
                                       class="nav-link {{ Request::is('waste-requests')?'active':'' }}"
                                       data-key="t-horizontal">Waste Requests</a>
                                </li>
                            @endcanany

                            @canany(['000246','000247'])
                                <li class="nav-item">
                                    <a href="{{url('fileUpload/admin')}}"
                                       class="nav-link {{ Request::is('fileUpload/admin')?'active':'' }}"
                                       data-key="t-horizontal">File Upload</a>
                                </li>
                            @endcanany
                        </ul>
                    </div>
                </li>
                @endcanany


                
{{--                @endif--}}


                {{-- logout menu --}}
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('lookup/admin')?'active':'' }}"
                       href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="mdi mdi-logout fs-16 align-middle me-1"></i>
                        <span data-key="t-layouts">Logout ({{ Auth::user()->employee->full_name??Auth::user()->username}})</span>
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
                <!-- end Dashboard Menu -->
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
