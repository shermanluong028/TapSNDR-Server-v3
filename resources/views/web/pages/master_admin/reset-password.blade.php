@php
    $pageId = 'tapsndr-reset_password';
@endphp

<!DOCTYPE html>

<html lang="en">

<head>
    <title>TapSNDR | Reset Password</title>
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
                <div class="w-lg-550px bg-body rounded shadow-sm p-10 p-lg-15 mx-auto">
                    <form
                        class="form w-100 {{ $pageId }}-form"
                        novalidate="novalidate"
                        data-kt-redirect-url="/seven-html-pro/authentication/sign-in/basic.html"
                        id="kt_new_password_form"
                    >
                        @csrf
                        <input
                            type="hidden"
                            name="token"
                            value="{{ request('token') }}"
                        >
                        <div class="text-center mb-10">
                            <h1 class="text-gray-900 mb-3">
                                Setup New Password
                            </h1>

                            <div class="text-gray-500 fw-semibold fs-4">
                                Already have reset your password ?

                                <a
                                    href="{{ url('/auth/signin') }}"
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
                                <h4 class="mb-1 text-success">Password reset successfully!</h4>
                                <span>You can now sign in with your new password.</span>
                            </div>
                        </div>
                        <div
                            class="alert alert-danger d-flex align-items-center p-5 d-none {{ $pageId }}-alert-could_not_reset">
                            <i class="ki-duotone ki-cross-circle fs-2hx text-danger me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-danger">Oops!</h4>
                                <span>We couldn't reset your password. Please check the reset link and try again.</span>
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
                                readonly
                                value="{{ request('email') }}"
                            />
                        </div>
                        <div
                            class="mb-10 fv-row"
                            data-kt-password-meter="true"
                        >
                            <div {{-- class="mb-1" --}}>
                                <label class="form-label fw-bold text-gray-900 fs-6">
                                    Password
                                </label>
                                <div
                                    {{-- class="position-relative mb-3" --}}
                                    class="position-relative"
                                >
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
                                {{-- <div
                                    class="d-flex align-items-center mb-3"
                                    data-kt-password-meter-control="highlight"
                                >
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px me-2"></div>
                                    <div class="flex-grow-1 bg-secondary bg-active-success rounded h-5px"></div>
                                </div> --}}
                            </div>
                            {{-- <div class="text-muted">
                                Use 8 or more characters with a mix of letters, numbers & symbols.
                            </div> --}}
                        </div>
                        <div class="fv-row mb-10">
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
                                class="btn btn-lg btn-primary fw-bold {{ $pageId }}-btn-submit"
                            >
                                Submit
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
        src="{{ URL::asset('assets/web/js/pages/master_admin/reset-password.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/app.js') }}"
    ></script>
</body>

</html>
