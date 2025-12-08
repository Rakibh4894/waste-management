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
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{route('dashboard')}}" role="button" aria-expanded="false"
                       aria-controls="sidebarDashboards">
                        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboard</span>
                    </a>
                </li>

                <!-- User Role -->
                @canany(['MANAGE_USERS', 'MANAGE_PERMISSION', 'MANAGE_ROLE'])
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
                                       aria-controls="sidebarCalendar">
                                        Role
                                    </a>
                                    <div class="collapse menu-dropdown {{ Request::is('roles/admin')||Request::is('roles/permission-assign/*')?'show':'' }}" id="sidebarCalendar">
                                        <ul class="nav nav-sm flex-column">
                                            <li class="nav-item">
                                                <a href="{{url('roles/admin')}}"
                                                   class="nav-link {{ Request::is('roles/admin')||Request::is('roles/permission-assign/*')?'active':'' }}"> Manage Roles </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            @endcanany
                            @canany(['MANAGE_PERMISSION'])
                                <li class="nav-item">
                                    <a href="#sidebarEmail"
                                       class="nav-link {{ Request::is('permissions/admin')?'active':'' }}"
                                       data-bs-toggle="collapse" role="button" aria-expanded="false"
                                       aria-controls="sidebarEmail">
                                        Permission
                                    </a>
                                    <div class="collapse menu-dropdown {{ Request::is('permissions/admin')?'show':'' }}" id="sidebarEmail">
                                        <ul class="nav nav-sm flex-column">
                                            <li class="nav-item">
                                                <a href="{{url('permissions/admin')}}"
                                                   class="nav-link {{ Request::is('permissions/admin')?'active':'' }}"> Admin </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            @endcanany
                            @canany(['MANAGE_USERS'])
                                <li class="nav-item">
                                    <a href="#sidebarEcommerce"
                                       class="nav-link {{ (Request::is('users/manage-users')||Request::is('users/manage-users-permission')||Request::is('users/assign-revoke-permission/*'))?'active':'' }}"
                                       data-bs-toggle="collapse" role="button" aria-expanded="false"
                                       aria-controls="sidebarEcommerce">
                                        Users
                                    </a>
                                    <div class="collapse menu-dropdown {{ (Request::is('users/manage-users')||Request::is('users/manage-users-permission')||Request::is('users/assign-revoke-permission/*'))?'show':'' }}" id="sidebarEcommerce">
                                        <ul class="nav nav-sm flex-column">
                                            @canany(['MANAGE_USERS'])
                                                <li class="nav-item">
                                                    <a href="{{url('users/manage-users')}}"
                                                       class="nav-link {{ Request::is('users/manage-users')?'active':'' }}"> Manage Users </a>
                                                </li>
                                            @endcanany
                                            @canany(['000000','000263'])
                                                <li class="nav-item">
                                                    <a href="{{url('users/manage-users-permission')}}"
                                                       class="nav-link {{ (Request::is('users/manage-users-permission')||Request::is('users/assign-revoke-permission/*'))?'active':'' }}"> Manage Permission </a>
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

                <!-- Waste Manage -->
                @canany(['MANAGE_WR','000247'])
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('waste-requests')||Request::is('wards')||Request::is('city-corporations')?'active':'' }}"
                       href="#sidebarWasteManage"
                       data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarWasteManage">
                        <i class="ri-layout-3-line"></i> <span data-key="t-layouts">Waste Manage</span>
                    </a>
                    <div class="collapse menu-dropdown {{ Request::is('waste-requests')||Request::is('wards')||Request::is('city-corporations')?'show':'' }}" id="sidebarWasteManage">
                        <ul class="nav nav-sm flex-column">
                            @canany(['MANAGE_CITY_CORPORATIONS'])
                                <li class="nav-item">
                                    <a href="{{url('city-corporations')}}"
                                       class="nav-link {{ Request::is('city-corporations')?'active':'' }}">City Corporations</a>
                                </li>
                            @endcanany
                            @canany(['MANAGE_WARDS'])
                                <li class="nav-item">
                                    <a href="{{url('wards')}}"
                                       class="nav-link {{ Request::is('wards')?'active':'' }}">Wards</a>
                                </li>
                            @endcanany
                            @canany(['WR_ADD', 'WR_EDIT','WR_DELETE', 'MANAGE_WR'])
                                <li class="nav-item">
                                    <a href="{{url('waste-requests')}}"
                                       class="nav-link {{ Request::is('waste-requests')?'active':'' }}">Waste Requests</a>
                                </li>
                            @endcanany
                        </ul>
                    </div>
                </li>
                @endcanany

                <!-- Recycle Manage -->
                @canany(['MANAGE_RP'])
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('recycle-process*') ? 'active' : '' }}"
                    href="#sidebarRecycleManage"
                    data-bs-toggle="collapse" role="button"
                    aria-expanded="{{ Request::is('recycle-process*') ? 'true' : 'false' }}"
                    aria-controls="sidebarRecycleManage">
                        <i class="ri-layout-3-line"></i> <span>Recycle Manage</span>
                    </a>

                    <div class="collapse menu-dropdown {{ Request::is('recycle-process*') ? 'show' : '' }}"
                        id="sidebarRecycleManage">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ url('recycle-process') }}"
                                class="nav-link {{ Request::is('recycle-process*') ? 'active' : '' }}">
                                    Recycle Requests
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                @endcanany


                <!-- BILL Manage -->
                @canany(['MANAGE_BILL'])
                <li class="nav-item">
                    <a class="nav-link menu-link {{ Request::is('payments*') || Request::is('monthly-bill*') ? 'active' : '' }}"
                    href="#sidebarPaymentsManage"
                    data-bs-toggle="collapse" role="button"
                    aria-expanded="{{ Request::is('payments*') || Request::is('monthly-bill*') ? 'true' : 'false' }}"
                    aria-controls="sidebarPaymentsManage">
                        <i class="ri-layout-3-line"></i> <span>Bill Payment</span>
                    </a>

                    <div class="collapse menu-dropdown {{ Request::is('payments*') || Request::is('monthly-bill*') ? 'show' : '' }}"
                        id="sidebarPaymentsManage">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('monthly-bill.index') }}"
                                class="nav-link {{ Request::is('monthly-bill*') ? 'active' : '' }}">
                                    Bill Amounts
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ url('payments') }}"
                                class="nav-link {{ Request::is('payments*') ? 'active' : '' }}">
                                    Manage Payment
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                @endcanany

                <!-- Logout -->
                <li class="nav-item">
                    @if(Auth::check())
                        <a class="nav-link menu-link" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-logout fs-16 align-middle me-1"></i>
                            <span>Logout ({{ Auth::user()->name }})</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    @else
                        <a class="nav-link menu-link" href="{{ route('login') }}">
                            <i class="mdi mdi-login fs-16 align-middle me-1"></i>
                            <span>Login</span>
                        </a>
                    @endif
                </li>
            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>
