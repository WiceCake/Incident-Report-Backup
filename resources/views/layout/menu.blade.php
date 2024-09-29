<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.html" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/muti/logo_header.png') }}" width="200px" alt="">
            </span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
        </a>
    </div>


    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboards -->
        <li class="menu-item {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-smile"></i>
                <div class="text-truncate" data-i18n="Dashboards">Dashboards</div>
            </a>
        </li>

        <!-- Front Pages -->
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-scatter-chart"></i>
                <div class="text-truncate" data-i18n="Analytics">Analytics</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="http://localhost:5601/" class="menu-link" target="_blank">
                        <div class="text-truncate" data-i18n="Incident reports">Kibana</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Reports -->
        <li class="menu-item
            @if (in_array(Route::currentRouteName(), ['report.security', 'report.incident'])) active open @endif
        ">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-error-alt"></i>
                <div class="text-truncate" data-i18n="Reports">Reports</div>
            </a>

            <ul class="menu-sub">
                <li class="menu-item {{ Route::currentRouteName() == 'report.security' ? 'active' : '' }}">
                    <a href="{{ route('report.security') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Incident reports">Security Events</div>
                    </a>
                </li>
                <li class="menu-item {{ Route::currentRouteName() == 'report.incident' ? 'active' : '' }}">
                    <a href="{{ route('report.incident') }}" class="menu-link">
                        <div class="text-truncate" data-i18n="Manage reports">Manage Reports</div>
                    </a>
                </li>
            </ul>
        </li>


        <!-- Logs Management -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Logs</span>
        </li>
        <li class="menu-item {{ Route::currentRouteName() == 'logs.user' ? 'active' : '' }}">
            <a href="{{ route('logs.user') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-user"></i>
                <div class="text-truncate" data-i18n="User Logs">User Logs</div>
            </a>
        </li>
        <li class="menu-item {{ Route::currentRouteName() == 'logs.honeypot' ? 'active' : '' }}">
            <a href="{{ route('logs.honeypot') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cube-alt"></i>
                <div class="text-truncate" data-i18n="Honeypot Logs">Honeypot Logs</div>
            </a>
        </li>

    </ul>
</aside>
