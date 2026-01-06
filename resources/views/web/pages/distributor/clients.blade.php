@php
    $pageId = 'tapsndr-clients';
@endphp

@extends('web.parts.layout')

@push('styles')
    <link
        href="{{ URL::asset('assets/web/css/pages/distributor/clients.css') }}"
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
                <h1 class="text-gray-900 fw-bold my-1 fs-2">Clients</h1>
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
                <div class="card">
                    <div class="card-body">
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
    @include('web.parts.modals.tickets', ['assignedId' => $pageId . '-modal-tickets'])
    @include('web.parts.modals.fulfiller', ['assignedId' => $pageId . '-modal-fulfiller'])
    @include('web.parts.modals.client', ['assignedId' => $pageId . '-modal-client'])
@endpush

@push('drawers')
    @include('web.parts.drawers.user', ['assignedId' => $pageId . '-drawer-user'])
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
        src="{{ URL::asset('assets/web/js/pages/distributor/clients.js') }}"
    ></script>
@endpush
