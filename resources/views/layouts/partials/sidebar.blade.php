<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('dashboard') }}">
                <img src="{{ asset('assets/images/brand/logo-white.png') }}" class="header-brand-img desktop-logo"
                    alt="logo">
                <img src="{{ asset('assets/images/brand/icon-white.png') }}" class="header-brand-img toggle-logo"
                    alt="logo">
                <img src="{{ asset('assets/images/brand/icon-dark.png') }}" class="header-brand-img light-logo"
                    alt="logo">
                <img src="{{ asset('assets/images/brand/logo-dark.png') }}" class="header-brand-img light-logo1"
                    alt="logo">
            </a>
            <!-- LOGO -->
        </div>
        <div class="main-sidemenu">
            <ul class="side-menu">
                <li class="slide">
                    <a class="side-menu__item has-link {{ handleActiveSidebar(['dashboard']) }}" data-bs-toggle="slide"
                        href="{{ route('dashboard') }}"><i class="side-menu__icon fe fe-home"></i><span
                            class="side-menu__label">@lang('dashboard.dashboard')</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item has-link {{ handleActiveSidebar(['users.*']) }}" data-bs-toggle="slide"
                        href="{{ route('users.index') }}"><i class="side-menu__icon fe fe-users"></i><span
                            class="side-menu__label">@lang('dashboard.users')</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item has-link {{ handleActiveSidebar(['countries.*']) }}" data-bs-toggle="slide"
                        href="{{ route('countries.index') }}"><i class="side-menu__icon fe fe-flag"></i><span
                            class="side-menu__label">@lang('dashboard.Countries')</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item has-link {{ handleActiveSidebar(['cities.*']) }}" data-bs-toggle="slide"
                        href="{{ route('cities.index') }}"><i class="side-menu__icon fe fe-map-pin"></i><span
                            class="side-menu__label">@lang('dashboard.Cities')</span></a>
                </li>
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="javascript:void(0)"><i
                            class="side-menu__icon fe fe-slack"></i><span
                            class="side-menu__label">@lang('dashboard.users')</span><i class="angle fe fe-chevron-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li class="panel sidetab-menu">
                            <div class="panel-body tabs-menu-body p-0 border-0">
                                <div class="tab-content">
                                    <div class="tab-pane" id="side1">
                                        <ul class="sidemenu-list">
                                            <li><a href="cards.html" class="slide-item"> @lang('dashboard.users')</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </li>
            </ul>

        </div>
    </div>
</div>
