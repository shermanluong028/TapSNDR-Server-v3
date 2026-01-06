@php
    // $currentUser = Auth::user();
    // if (!$currentUser) {
    //     $currentUser = new \App\Models\User();
    //     $currentUser->role = 'guest';
    // }
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <title>{{ 'TapSNDR' . (isset($title) ? ' | ' . $title : '') }}</title>
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
        href="{{ URL::asset('assets/web/plugins/skeleton-screen-css/index.min.css') }}"
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
    @stack('styles')
    <script>
        // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking)
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
</head>

<body
    id="kt_body"
    class="header-fixed header-tablet-and-mobile-fixed toolbar-enabled aside-fixed aside-default-enabled"
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
        <div class="page d-flex flex-row flex-column-fluid">
            <div
                id="kt_aside"
                class="aside aside-default aside-hoverable"
                data-kt-drawer="true"
                data-kt-drawer-name="aside"
                data-kt-drawer-activate="{default: true, lg: false}"
                data-kt-drawer-overlay="true"
                data-kt-drawer-width="{default:'200px', '300px': '250px'}"
                data-kt-drawer-direction="start"
                data-kt-drawer-toggle="#kt_aside_toggle"
            >
                <div
                    class="aside-logo flex-column-auto px-10 pt-9 pb-5 justify-content-center"
                    id="kt_aside_logo"
                >
                    <a href="{{ url('/') }}">
                        <img
                            alt="Logo"
                            src="{{ URL::asset('assets/web/media/logos/logo-default.png') }}"
                            class="mh-65px logo-default theme-light-show"
                        >
                        <img
                            alt="Logo"
                            src="{{ URL::asset('assets/web/media/logos/logo-dark.png') }}"
                            class="mh-65px logo-default theme-dark-show"
                        >
                    </a>
                </div>
                @if (View::exists('web.parts.layout.menu.' . $currentUser->role))
                    @include('web.parts.layout.menu.' . $currentUser->role)
                @else
                    @include('web.parts.layout.menu.empty')
                @endif
            </div>
            <div
                class="wrapper d-flex flex-column flex-row-fluid"
                id="kt_wrapper"
            >
                @if (View::exists('web.parts.layout.header.' . $currentUser->role))
                    @include('web.parts.layout.header.' . $currentUser->role)
                @else
                    @include('web.parts.layout.header.empty')
                @endif
                <div
                    class="content d-flex flex-column flex-column-fluid"
                    id="kt_content"
                >
                    @yield('content')
                </div>
                <div
                    class="footer py-4 d-flex flex-lg-column"
                    id="kt_footer"
                >
                    <div class="container-fluid d-flex flex-column flex-md-row flex-stack">
                        <div class="text-gray-900 order-2 order-md-1">
                            {{-- <span class="text-muted fw-semibold me-2">2024&copy;</span> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @stack('modals')
    @stack('drawers')
    <div
        id="kt_scrolltop"
        class="scrolltop"
        data-kt-scrolltop="true"
    >
        <i class="ki-duotone ki-arrow-up">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </div>
    <script>
        var hostUrl = "{{ URL::asset('/') }}";
    </script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/plugins.bundle.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/lodash-4.17.21/package/lodash.min.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/accounting.js/accounting.min.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/utils.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/scripts.bundle.js') }}"
    ></script>
    @include('web.parts.csrfToken')
    @include('web.parts.serverUrl')
    @include('web.parts.currentUser')
    @include('web.parts.data')
    @stack('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/app.js') }}"
    ></script>
</body>

</html>
