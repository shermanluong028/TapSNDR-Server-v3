@php
    $pageId = 'tapsndr-account';
@endphp

@extends('web.parts.layout')

@push('styles')
    <link
        href="{{ URL::asset('assets/web/css/pages/master_admin/account.css') }}"
        rel="stylesheet"
        type="text/css"
    />
    <link
        href="{{ URL::asset('assets/web/plugins/datatables/datatables.bundle.css') }}"
        rel="stylesheet"
        type="text/css"
    />
@endpush

@section('content')
    <div
        class="toolbar"
        id="kt_toolbar"
    >
        <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
            <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                <h1 class="text-gray-900 fw-bold my-1 fs-2">Account Details</h1>
            </div>
        </div>
    </div>
    <div
        class="post fs-6 d-flex flex-column-fluid"
        id="kt_post"
    >
        <div class="container-xxl">
            <div class="card mb-5 mb-xl-10">
                <div class="card-body pt-9 pb-0">
                    <div class="d-flex flex-wrap flex-sm-nowrap mb-3">
                        <div class="me-7 mb-4">
                            <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                <img
                                    src="{{ URL::asset('assets/web/media/svg/user.svg') }}"
                                    alt="image"
                                    class="theme-light-show"
                                />
                                <img
                                    src="{{ URL::asset('assets/web/media/svg/user-dark.svg') }}"
                                    alt="image"
                                    class="theme-dark-show"
                                />
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center mb-2">
                                        <a
                                            href="javascript:void(0);"
                                            class="text-gray-900 text-hover-primary fs-2 fw-bold me-1 {{ $pageId }}-username"
                                        >
                                            <span class="ssc-square w-100px h-30px"></span>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                        <a
                                            href="javascript:void(0);"
                                            class="d-flex align-items-center text-gray-500 text-hover-primary me-5 mb-2 {{ $pageId }}-role"
                                        >
                                            <i class="ki-duotone ki-profile-circle fs-4 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            <span>
                                                <span class="ssc-square w-60px h-15px"></span>
                                            </span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row gy-5 g-xl-10">
                <div class="col-xl-6">
                    <div class="card card-flush h-xl-100 {{ $pageId }}-pending_deposits">
                        <div class="card-header">
                            <h3 class="card-title">
                                <span class="card-label fw-bold text-gray-900">Pending deposits</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <table class="table table-striped table-row-bordered gy-5 gs-7"></table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/datatables/datatables.bundle.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/pages/master_admin/account.js') }}"
    ></script>
@endpush
