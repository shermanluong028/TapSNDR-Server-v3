@php
    $pageId = 'tapsndr-forgot_password';
@endphp

<!DOCTYPE html>

<html lang="en">

<head>
    <title>TapSNDR | Forgot Password</title>
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
        rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700"
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
        var defaultThemeMode = "light";
        var themeMode;

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
            class="d-flex flex-column flex-column-fluid bgi-position-y-bottom position-x-center bgi-no-repeat bgi-size-contain bgi-attachment-fixed">
            <div class="d-flex flex-center flex-column flex-column-fluid p-10 pb-lg-20">
                <a
                    href="/seven-html-pro/index.html"
                    class="mb-12"
                >
                    <img
                        alt="Logo"
                        src="{{ URL::asset('assets/web/media/logos/logo-default.png') }}"
                        class="h-60px"
                    />
                </a>
                <div class="w-lg-500px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
                    <form
                        class="form w-100 {{ $pageId }}-form"
                        novalidate="novalidate"
                    >
                        @csrf
                        <div class="text-center mb-10">
                            <h1 class="text-gray-900 mb-3">
                                Forgot Password ?
                            </h1>
                            <div class="text-gray-500 fw-semibold fs-4">
                                Enter your email to reset your password.
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
                                <h4 class="mb-1 text-success">Password reset link sent!</h4>
                                <span>Check your inbox. We have emailed your password reset link.</span>
                            </div>
                        </div>
                        <div
                            class="alert alert-danger d-flex align-items-center p-5 d-none {{ $pageId }}-alert-user_not_found">
                            <i class="ki-duotone ki-cross-circle fs-2hx text-danger me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-danger">Oops!</h4>
                                <span>We couldn't find an account with that email address.</span>
                            </div>
                        </div>
                        <div
                            class="alert alert-danger d-flex align-items-center p-5 d-none {{ $pageId }}-alert-could_not_send_email">
                            <i class="ki-duotone ki-cross-circle fs-2hx text-danger me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-danger">Oops!</h4>
                                <span>We couldn't send the password reset email. Please try again later.</span>
                            </div>
                        </div>
                        <div class="fv-row mb-10">
                            <label class="form-label fw-bold text-gray-900 fs-6">Email</label>
                            <input
                                class="form-control form-control-solid"
                                type="email"
                                placeholder=""
                                name="email"
                                autocomplete="off"
                            />
                        </div>
                        <div class="d-flex flex-wrap justify-content-center pb-lg-0">
                            <button
                                type="submit"
                                class="btn btn-lg btn-primary fw-bold me-4 {{ $pageId }}-btn-submit"
                            >
                                Submit
                            </button>
                            <a
                                href="{{ url('/auth/signin') }}"
                                class="btn btn-lg btn-light-primary fw-bold"
                            >
                                Cancel
                            </a>
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
        src="{{ URL::asset('assets/web/js/pages/master_admin/forgot-password.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/app.js') }}"
    ></script>
</body>

</html>
