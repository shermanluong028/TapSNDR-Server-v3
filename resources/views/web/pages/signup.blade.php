@php
    $pageId = 'tapsndr-signup';
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>TapSNDR | Sign Up</title>
    <meta charset="utf-8" />
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    />
    <link
        rel="shortcut icon"
        href="{{ URL::asset('assets/web/media/logos/favicon-32x32.png') }}"
    />
    <link
        href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"
        rel="stylesheet"
        type="text/css"
    />
    <link
        href="{{ URL::asset('assets/web/css/plugins.bundle.css') }}"
        rel="stylesheet"
        type="text/css"
    />
    <link
        href="{{ URL::asset('assets/web/css/style.bundle.css') }}"
        rel="stylesheet"
        type="text/css"
    />
    <link
        href="{{ URL::asset('assets/web/css/app.css') }}"
        rel="stylesheet"
        type="text/css"
    />
    <script>
        // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking)
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
</head>

<body
    id="kt_body"
    class="auth-bg"
>
    <script>
        const defaultThemeMode = "light";
        let themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <div class="d-flex flex-column flex-root">
        <div
            class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed auth-page-bg">
            <div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
                <a
                    href="{{ url('/') }}"
                    class="mb-12"
                >
                    <img
                        alt="Logo"
                        src="{{ URL::asset('assets/web/media/logos/logo-default.png') }}"
                        class="h-60px"
                    />
                </a>
                <div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
                    <form class="form w-100 {{ $pageId }}-form">
                        @csrf
                        <div class="text-center mb-10">
                            <h1 class="text-gray-900 mb-3">Create an Account</h1>
                            <div class="text-gray-500 fw-semibold fs-4">
                                Already have an account?
                                <a
                                    href="{{ url('/auth/signin?redirect_to=' . urlencode(request()->input('redirect_to'))) }}"
                                    class="link-primary fw-bold"
                                >
                                    Sign in here
                                </a>
                            </div>
                        </div>
                        <div
                            class="alert alert-success d-flex align-items-center p-5 d-none {{ $pageId }}-alert-success">
                            <i class="ki-duotone ki-user-tick fs-2hx text-success me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-success">Account created!</h4>
                                <span>You will be redirected shortly.</span>
                            </div>
                        </div>
                        <div class="fv-row mb-10">
                            <label class="form-label fs-6 fw-bold text-gray-900">Email</label>
                            <input
                                class="form-control form-control-lg form-control-solid"
                                type="email"
                                placeholder=""
                                name="email"
                                autocomplete="off"
                            />
                        </div>
                        <div
                            class="mb-7 fv-row"
                            data-kt-password-meter="true"
                        >
                            <div class="d-flex flex-stack mb-2">
                                <label class="form-label fw-bold text-gray-900 fs-6 mb-0">Password</label>
                            </div>
                            <div class="position-relative">
                                <input
                                    class="form-control form-control-lg form-control-solid"
                                    type="password"
                                    placeholder=""
                                    name="password"
                                    autocomplete="off"
                                />
                                <span
                                    class="btn btn-sm btn-icon position-absolute translate-middle top-50 end-0 me-n2"
                                    data-kt-password-meter-control="visibility"
                                >
                                    <i class="ki-duotone ki-eye fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <i class="ki-duotone ki-eye-slash fs-2 d-none">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                </span>
                            </div>
                        </div>
                        <div class="fv-row mb-5">
                            <label class="form-label fw-bold text-gray-900 fs-6">Confirm Password</label>
                            <input
                                class="form-control form-control-lg form-control-solid"
                                type="password"
                                placeholder=""
                                name="password1"
                                autocomplete="off"
                            />
                        </div>
                        <div class="text-center">
                            <button
                                type="submit"
                                class="btn btn-lg btn-primary w-100 mb-5 {{ $pageId }}-btn-signup"
                            >
                                Continue
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        const hostUrl = "{{ URL::asset('assets/') }}";
    </script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/plugins.bundle.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/scripts.bundle.js') }}"
    ></script>
    @include('web.parts.csrfToken')
    @include('web.parts.serverUrl')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/utils.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/jquery-validation-1.19.5/jquery.validate.min.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/jquery-validation-1.19.5/additional-methods.min.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/pages/signup.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/app.js') }}"
    ></script>
</body>

</html>
