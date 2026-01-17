<!doctype html>
<html lang="en" dir="ltr">
<head>
    <style>
        html {
            scrollbar-gutter: stable both-edges;
        }

        @supports not (scrollbar-gutter: stable) {
            html {
                overflow-y: scroll;
            }
        }

        .wrap-login100 {
            width: min(100%, 420px);
            margin-inline: auto;
        }

        .col-login {
            max-width: 100%;
        }

        .field-error {
            min-height: 1.25rem;
            font-size: .875rem;
        }

        .is-invalid, .is-valid, .input-group .form-control {
            transition: box-shadow .2s, border-color .2s; /* بدون width/padding */
        }
    </style>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/images/brand/favicon.ico')}}">
    <title>Login</title>

    <link id="style" href="{{asset('assets/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/style.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/plugins.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">
    <link href="{{asset('assets/switcher/css/switcher.css')}}" rel="stylesheet">
    <link href="{{asset('assets/switcher/demo.css')}}" rel="stylesheet">
</head>

<body class="app sidebar-mini ltr login-img">

<div class="">
    <div id="global-loader">
        <img src="{{asset('assets/images/loader.svg')}}" class="loader-img" alt="Loader">
    </div>

    <div class="page">
        <div class="">
            <div class="col col-login mx-auto mt-7">
                <div class="text-center">
                    <a href="#"><img src="{{asset('images/logo.png')}}" class="header-brand-img"
                                     alt=""></a>
                </div>
            </div>

            <div class="container-login100">
                <div class="wrap-login100 p-6">
                    <form action="{{ route('login') }}" method="POST" class="login100-form validate-form"
                          novalidate>
                        @csrf

                        <span class="login100-form-title pb-5">Login</span>

                        {{-- Email --}}
                        <div class="wrap-input100 validate-input input-group mb-3"
                             data-bs-validate="Valid email is required: ex@abc.xyz">
                            <span class="input-group-text bg-white text-muted">
                                <i class="zmdi zmdi-email text-muted" aria-hidden="true"></i>
                            </span>
                            <input
                                type="email"
                                name="email"
                                class="input100 border-start-0 form-control ms-0 @error('email') is-invalid @enderror"
                                placeholder="Email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                autofocus
                            >
                            <div class="invalid-feedback d-block field-error">
                                @error('email') {{ $message }} @else &nbsp; @enderror
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="wrap-input100 validate-input input-group mb-3" id="Password-toggle">
                            <span class="input-group-text bg-white text-muted">
                                <i class="zmdi zmdi-eye text-muted" aria-hidden="true"></i>
                            </span>
                            <input
                                type="password"
                                name="password"
                                class="input100 border-start-0 form-control ms-0 @error('password') is-invalid @enderror"
                                placeholder="Password"
                                required
                                autocomplete="current-password"
                            >
                            <div class="invalid-feedback d-block field-error">
                                @error('password') {{ $message }} @else &nbsp; @enderror
                            </div>
                        </div>

                        {{-- Remember me --}}
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember"
                                       id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                        </div>

                        <div class="container-login100-form-btn">
                            <button type="submit" class="login100-form-btn btn-primary w-100">Login</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

</div>

<script src="{{asset('assets/js/jquery.min.js')}}"></script>
<script src="{{asset('assets/plugins/bootstrap/js/popper.min.js')}}"></script>
<script src="{{asset('assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>
<script src="{{asset('assets/js/show-password.min.js')}}"></script>
<script src="{{asset('assets/plugins/p-scroll/perfect-scrollbar.js')}}"></script>
<script src="{{asset('assets/js/themeColors.js')}}"></script>
<script src="{{asset('assets/js/custom.js')}}"></script>
<script src="{{asset('assets/js/custom-swicher.js')}}"></script>
<script src="{{asset('assets/switcher/js/switcher.js')}}"></script>

{{-- SweetAlert2 Toast --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@include('layouts.partials.toast')
</body>
</html>
