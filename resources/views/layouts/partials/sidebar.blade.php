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
                @can('dashboard.view')
                    <li class="slide">
                        <a class="side-menu__item has-link {{ handleActiveSidebar(['dashboard']) }}" data-bs-toggle="slide"
                            href="{{ route('dashboard') }}"><i class="side-menu__icon fe fe-home"></i><span
                                class="side-menu__label">@lang('dashboard.dashboard')</span></a>
                    </li>
                @endcan
                @can('users.view')
                    <li class="slide">
                        <a class="side-menu__item {{ handleActiveSidebar(['users.*']) }}" data-bs-toggle="slide"
                            href="javascript:void(0)"><i class="side-menu__icon fe fe-users"></i><span
                                class="side-menu__label">@lang('dashboard.users')</span><i class="angle fe fe-chevron-right"></i>
                        </a>
                        <ul class="slide-menu">
                            <li><a href="{{ route('users.all') }}"
                                    class="slide-item {{ handleActiveSidebar(['users.all']) }}">@lang('dashboard.All')</a></li>
                            <li><a href="{{ route('users.users') }}"
                                    class="slide-item {{ handleActiveSidebar(['users.users']) }}">@lang('dashboard.users')</a>
                            </li>
                            <li><a href="{{ route('users.vendors') }}"
                                    class="slide-item {{ handleActiveSidebar(['users.vendors']) }}">@lang('dashboard.vendors')</a>
                            </li>
                            <li><a href="{{ route('users.admins') }}"
                                    class="slide-item {{ handleActiveSidebar(['users.admins']) }}">@lang('dashboard.admins')</a>
                            </li>
                        </ul>
                    </li>
                @endcan
                @can('countries.view')
                    <li class="slide">
                        <a class="side-menu__item has-link {{ handleActiveSidebar(['countries.*']) }}"
                            data-bs-toggle="slide" href="{{ route('countries.index') }}"><i
                                class="side-menu__icon fe fe-flag"></i><span
                                class="side-menu__label">@lang('dashboard.Countries')</span></a>
                    </li>
                @endcan
                @can('cities.view')
                    <li class="slide">
                        <a class="side-menu__item has-link {{ handleActiveSidebar(['cities.*']) }}" data-bs-toggle="slide"
                            href="{{ route('cities.index') }}"><i class="side-menu__icon fe fe-map-pin"></i><span
                                class="side-menu__label">@lang('dashboard.Cities')</span></a>
                    </li>
                @endcan
                @can('categories.view')
                    <li class="slide">
                        <a class="side-menu__item has-link {{ handleActiveSidebar(['categories.*']) }}"
                            data-bs-toggle="slide" href="{{ route('categories.index') }}"><i
                                class="side-menu__icon fe fe-tag"></i><span
                                class="side-menu__label">@lang('dashboard.Categories')</span></a>
                    </li>
                @endcan
                @can('services.view')
                    <li class="slide">
                        <a class="side-menu__item has-link {{ handleActiveSidebar(['services.*']) }}"
                            data-bs-toggle="slide" href="{{ route('services.index') }}"><i
                                class="side-menu__icon fe fe-settings"></i><span
                                class="side-menu__label">@lang('dashboard.Services')</span></a>
                    </li>
                @endcan
                {{-- @can('products.view')
                    <li class="slide">
                        <a class="side-menu__item has-link {{ handleActiveSidebar(['products.*']) }}"
                            data-bs-toggle="slide" href="{{ route('products.index') }}"><i
                                class="side-menu__icon fe fe-package"></i><span
                                class="side-menu__label">@lang('dashboard.Products')</span></a>
                    </li>
                @endcan --}}
                @can('roles.view')
                    <li class="slide">
                        <a class="side-menu__item has-link {{ handleActiveSidebar(['roles.*']) }}" data-bs-toggle="slide"
                            href="{{ route('roles.index') }}"><i class="side-menu__icon fe fe-shield"></i><span
                                class="side-menu__label">@lang('dashboard.Roles')</span></a>
                    </li>
                @endcan
                @can('tickets.view')
                    <li class="slide">
                        <a class="side-menu__item has-link {{ handleActiveSidebar(['tickets.*']) }}" data-bs-toggle="slide"
                            href="{{ route('tickets.index') }}"><i class="side-menu__icon fa fa-ticket"></i><span
                                class="side-menu__label">@lang('dashboard.Tickets')</span></a>
                    </li>
                @endcan
                @can('offers.view')
                    <li class="slide">
                        <a class="side-menu__item has-link {{ handleActiveSidebar(['offers.*']) }}" data-bs-toggle="slide"
                            href="{{ route('offers.index') }}"><i class="side-menu__icon fa fa-gift"></i><span
                                class="side-menu__label">@lang('dashboard.Offers')</span></a>
                    </li>
                @endcan
                <li class="slide">
                    <a class="side-menu__item has-link" data-bs-toggle="slide" href="{{ url('translations') }}"
                        target="_blank"><i class="side-menu__icon fa fa-language"></i><span
                            class="side-menu__label">@lang('dashboard.translations')</span></a>
                </li>
            </ul>

        </div>
    </div>
</div>
