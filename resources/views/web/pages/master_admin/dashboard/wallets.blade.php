@php
    $pageId = 'tapsndr-dashboard-wallets';
@endphp

@extends('web.parts.layout')

@section('content')
    <div
        class="toolbar"
        id="kt_toolbar"
    >
        <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
            <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                <h1 class="text-gray-900 fw-bold my-1 fs-2">Wallets
                    <small class="text-muted fs-6 fw-normal ms-1"></small>
                </h1>
                <ul class="breadcrumb fw-semibold fs-base my-1">
                    <li class="breadcrumb-item text-muted">Dashboards</li>
                    <li class="breadcrumb-item text-gray-900">Wallets</li>
                </ul>
            </div>
        </div>
    </div>
    <div
        class="post fs-6 d-flex flex-column-fluid"
        id="kt_post"
    >
        <div class="container-xxl">
            <div class="row g-6 g-xl-9">
                <div class="col-lg-6 col-xxl-4">
                    <div class="card h-100">
                        <div class="card-body p-9 {{ $pageId }}-total_balance">
                            <div class="fs-2hx fw-bold d-flex align-items-center">$ --</div>
                            <div class="fs-4 fw-semibold text-gray-500 mb-7">Total Balance</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-xxl-4">
                    <div class="card h-100">
                        <div class="card-body p-9 {{ $pageId }}-total_wallet_amount">
                            <div class="fs-2hx fw-bold">
                                <div class="fs-2hx fw-bold">$ --</div>
                            </div>
                            <div class="fs-4 fw-semibold text-gray-500 mb-7">Total Wallet Amount</div>
                            <div>
                                <div class="fs-6 d-flex justify-content-between mb-4">
                                    <div class="ssc-line h-20px"></div>
                                </div>
                            </div>
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
        src="{{ URL::asset('assets/web/js/pages/master_admin/dashboard/wallets.js') }}"
    ></script>
@endpush
