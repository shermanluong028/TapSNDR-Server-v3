@php
    $pageId = 'tapsndr-dashboard-tickets';
@endphp

@extends('web.parts.layout')

@section('content')
    <div
        class="toolbar"
        id="kt_toolbar"
    >
        <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
            <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                <h1 class="text-gray-900 fw-bold my-1 fs-2">
                    Tickets
                    <small class="text-muted fs-6 fw-normal ms-1"></small>
                </h1>
                <ul class="breadcrumb fw-semibold fs-base my-1">
                    <li class="breadcrumb-item text-muted">Dashboards</li>
                    <li class="breadcrumb-item text-gray-900">Tickets</li>
                </ul>
            </div>
        </div>
    </div>
    <div
        class="post fs-6 d-flex flex-column-fluid"
        id="kt_post"
    >
        <div class="container-xxl">
            <div class="row gy-5 g-xxl-10">
                <div class="col-xxl-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="{{ $pageId }}-chart-count_by_status"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-8">
                    <div class="card card-flush h-100 {{ $pageId }}-daily_total_amount">
                        <div class="card-header pt-5 mb-[-20px]">
                            <h3 class="card-title">
                                <span class="card-label text-gray-900">
                                    Daily stats
                                </span>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap gap-5 mb-5">
                                <div>
                                    <select
                                        class="form-select form-select-solid w-200px"
                                        name="user_id"
                                    >
                                        <option value="">All Users</option>
                                    </select>
                                </div>
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        value=""
                                        id="{{ $pageId }}-daily_total_amount-control-up_to_current_time"
                                        name="up_to_current_time"
                                    />
                                    <label
                                        class="form-check-label"
                                        for="{{ $pageId }}-daily_total_amount-control-up_to_current_time"
                                    >
                                        Up To Current Time
                                    </label>
                                </div>
                            </div>
                            <div class="h-350px {{ $pageId }}-daily_total_amount-chart"></div>
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
        src="{{ URL::asset('assets/web/js/pages/master_admin/dashboard/tickets.js') }}"
    ></script>
@endpush
