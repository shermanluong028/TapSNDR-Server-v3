@php
    $pageId = 'tapsndr-settings-ticket-payment_methods';
@endphp

@extends('web.parts.layout')

@push('styles')
    <link
        href="{{ URL::asset('assets/web/css/pages/master_admin/settings/ticket/payment_methods.css') }}"
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
                <h1 class="text-gray-900 fw-bold my-1 fs-2">Payment Methods</h1>
                <ul class="breadcrumb fw-semibold fs-base my-1">
                    <li class="breadcrumb-item text-muted">Settings</li>
                    <li class="breadcrumb-item text-muted">Ticket</li>
                    <li class="breadcrumb-item text-gray-900">Payment Methods</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="d-flex flex-column-fluid">
        <div class="w-100 mx-auto px-10">
            <div class="row g-0">
                <div class="card">
                    <div class="card-body">
                        <table class="table table-row-dashed border-gray-300 align-middle gy-6 {{ $pageId }}-table">
                        </table>
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
        src="{{ URL::asset('assets/web/js/pages/master_admin/settings/ticket/payment_methods.js') }}"
    ></script>
@endpush
