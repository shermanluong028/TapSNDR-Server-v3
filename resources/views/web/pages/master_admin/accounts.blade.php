@php
    $pageId = 'tapsndr-accounts';
@endphp

@extends('web.parts.layout')

@push('styles')
    <link
        href="{{ URL::asset('assets/web/css/pages/master_admin/accounts.css') }}"
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
                <h1 class="text-gray-900 fw-bold my-1 fs-2">Accounts</h1>
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
            <div class="row g-xxl-10 my-5">
                <div class="col-3 mt-0">
                    <div class="card border-2 border-gray-500 cursor-pointer h-100 {{ $pageId }}-stats-count-total">
                        <div class="card-body d-flex flex-column align-items-center">
                            <span class="fs-2hx fw-bold text-gray-800">
                                <div class="spinner-border"></div>
                            </span>
                            <span class="fs-6 fw-semibold text-gray-500">Total</span>
                        </div>
                    </div>
                </div>
                <div class="col-3 mt-0">
                    <div
                        class="card border-2 border-pink cursor-pointer h-100 {{ $pageId }}-stats-count-distributors">
                        <div class="card-body d-flex flex-column align-items-center">
                            <span class="fs-2hx fw-bold text-gray-800">
                                <div class="spinner-border"></div>
                            </span>
                            <span class="fs-6 fw-semibold text-pink">Distributors</span>
                        </div>
                    </div>
                </div>
                <div class="col-3 mt-0">
                    <div
                        class="card border-2 border-primary cursor-pointer h-100 {{ $pageId }}-stats-count-fulfillers">
                        <div class="card-body d-flex flex-column align-items-center">
                            <span class="fs-2hx fw-bold text-gray-800">
                                <div class="spinner-border"></div>
                            </span>
                            <span class="fs-6 fw-semibold text-primary">Fulfillers</span>
                        </div>
                    </div>
                </div>
                <div class="col-3 mt-0">
                    <div class="card border-2 border-info cursor-pointer h-100 {{ $pageId }}-stats-count-clients">
                        <div class="card-body d-flex flex-column align-items-center">
                            <span class="fs-2hx fw-bold text-gray-800">
                                <div class="spinner-border"></div>
                            </span>
                            <span class="fs-6 fw-semibold text-info">Clients</span>
                        </div>
                    </div>
                </div>
            </div>
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
                                    placeholder="Search Users"
                                >
                            </div>
                        </div>
                        <div class="card-toolbar">
                            <div class="d-flex flex-center">
                                <label class="form-label m-0 me-3 text-nowrap">Sort By</label>
                                <select
                                    data-control="select2"
                                    {{-- data-hide-search="true" --}}
                                    class="form-select form-select-solid {{ $pageId }}-control-sort_by"
                                >
                                    <option value="created_at_desc">Created At ↓</option>
                                    <option value="created_at_asc">Created At ↑</option>
                                    <option value="balance_desc">Balance ↓&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
                                    <option value="balance_asc">Balance ↑&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</option>
                                    <option value="total_completed_ticket_amount_desc">
                                        Total Completed Ticket Amount ↓
                                    </option>
                                    <option value="total_completed_ticket_amount_asc">
                                        Total Completed Ticket Amount ↑
                                    </option>
                                    <option value="avg_daily_completed_ticket_amount_desc">
                                        Avg daily completed ticket amount ↓
                                    </option>
                                    <option value="avg_daily_completed_ticket_amount_asc">
                                        Avg daily completed ticket amount ↑
                                    </option>
                                </select>
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
    @include('web.parts.modals.transaction', ['assignedId' => $pageId . '-modal-transaction'])
    @include('web.parts.modals.tickets', ['assignedId' => $pageId . '-modal-tickets'])
    {{-- @include('web.parts.modals.fulfiller', ['assignedId' => $pageId . '-modal-fulfiller']) --}}
    {{-- @include('web.parts.modals.client', ['assignedId' => $pageId . '-modal-client']) --}}
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
        src="{{ URL::asset('assets/web/js/pages/master_admin/accounts.js') }}"
    ></script>
@endpush
