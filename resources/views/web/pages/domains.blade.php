@php
    $pageId = 'tapsndr-domains';
@endphp

@extends('web.parts.layout')

@push('styles')
    <link
        href="{{ URL::asset('assets/web/css/pages/domains.css') }}"
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
                <h1 class="text-gray-900 fw-bold my-1 fs-2">Vendors</h1>
            </div>
            <div class="d-flex align-items-center flex-nowrap text-nowrap py-1">
                <a
                    href="#"
                    class="btn btn-primary {{ $pageId }}-btn-create"
                >
                    Add new
                </a>
            </div>
        </div>
    </div>
    <div class="d-flex flex-column-fluid">
        <div class="w-100 mx-auto px-10">
            <div class="row g-0">
                <div class="card card-flush">
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <div class="card-title w-100 w-sm-unset me-0 me-sm-2">
                            <div class="d-flex align-items-center position-relative my-1 w-100 w-sm-unset">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <input
                                    type="text"
                                    class="form-control form-control-solid w-100 w-sm-250px ps-12 {{ $pageId }}-control-search_key"
                                    placeholder="Search Vendors"
                                >
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table table-striped table-row-bordered gy-5 gs-7 {{ $pageId }}-table"></table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    @include('web.parts.modals.commission_percentage', [
        'assignedId' => $pageId . '-modal-commission_percentage',
    ])
@endpush

@push('drawers')
    @include('web.parts.drawers.domain', ['assignedId' => $pageId . '-drawer-domain'])
@endpush

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/datatables/datatables.bundle.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/jquery-validation-1.19.5/jquery.validate.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/jquery-validation-1.19.5/additional-methods.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/pages/domains.js') }}"
    ></script>
@endpush
