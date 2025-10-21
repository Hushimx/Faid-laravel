<!doctype html>
<html lang="en" dir="ltr">

<head>

    <!-- META DATA -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- FAVICON -->
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('assets/images/brand/favicon.ico')}}">

    <!-- TITLE -->
    <title>Login</title>

    <!-- BOOTSTRAP CSS -->
    <link id="style" href="{{asset('assets/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">

    <!-- STYLE CSS -->
    <link href="{{asset('assets/css/style.css')}}" rel="stylesheet">

    <!-- Plugins CSS -->
    <link href="{{asset('assets/css/plugins.css')}}" rel="stylesheet">

    <!--- FONT-ICONS CSS -->
    <link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">

    <!-- INTERNAL Switcher css -->
    <link href="{{asset('assets/switcher/css/switcher.css')}}" rel="stylesheet">
    <link href="{{asset('assets/switcher/demo.css')}}" rel="stylesheet">

</head>

<body class="app sidebar-mini ltr login-img">

<!-- BACKGROUND-IMAGE -->
<div class="">

    <!-- GLOABAL LOADER -->
    <div id="global-loader">
        <img src="{{asset('assets/images/loader.svg')}}" class="loader-img" alt="Loader">
    </div>
    <!-- /GLOABAL LOADER -->

    <!-- PAGE -->
    <div class="page">
        <div class="">
            <!-- Theme-Layout -->

            <!-- CONTAINER OPEN -->
            <div class="col col-login mx-auto mt-7">
                <div class="text-center">
                    <a href="index.html"><img src="{{asset('assets/images/brand/logo-white.png')}}"
                                              class="header-brand-img"
                                              alt=""></a>
                </div>
            </div>

            <div class="container-login100">
                <div class="wrap-login100 p-6">
                    <form action="{{ route('login') }}" method="POST" class="login100-form validate-form">
                        @csrf

                        <span class="login100-form-title pb-5">
        Login
    </span>

                        {{-- Email --}}
                        <div class="wrap-input100 validate-input input-group"
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
                            @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="wrap-input100 validate-input input-group" id="Password-toggle">
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
                            @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Remember me --}}
                        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
                            <div class="form-check d-flex align-items-center ">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="remember"
                                    id="remember"
                                    {{ old('remember') ? 'checked' : '' }}
                                >
                                <label class="form-check-label d-flex align-items-center justify-content-center" for="remember">
                                    Remember me
                                </label>
                            </div>
                        </div>

                        <div class="container-login100-form-btn">
                            <button type="submit" class="login100-form-btn btn-primary w-100">
                                Login
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- CONTAINER CLOSED -->
        </div>
    </div>
    <!-- End PAGE -->

</div>
<!-- BACKGROUND-IMAGE CLOSED -->

<!-- JQUERY JS -->
<script src="{{asset('assets/js/jquery.min.js')}}"></script>

<!-- BOOTSTRAP JS -->
<script src="{{asset('assets/plugins/bootstrap/js/popper.min.js')}}"></script>
<script src="{{asset('assets/plugins/bootstrap/js/bootstrap.min.js')}}"></script>

<!-- SHOW PASSWORD JS -->
<script src="{{asset('assets/js/show-password.min.js')}}"></script>

<!-- GENERATE OTP JS -->
<script src="{{asset('assets/js/generate-otp.js')}}"></script>

<!-- Perfect SCROLLBAR JS-->
<script src="{{asset('assets/plugins/p-scroll/perfect-scrollbar.js')}}"></script>

<!-- Color Theme js -->
<script src="{{asset('assets/js/themeColors.js')}}"></script>

<!-- CUSTOM JS -->
<script src="{{asset('assets/js/custom.js')}}"></script>

<!-- Custom-switcher -->
<script src="{{asset('assets/js/custom-swicher.js')}}"></script>

<!-- Switcher js -->
<script src="{{asset('assets/switcher/js/switcher.js')}}"></script>

</body>

</html>
