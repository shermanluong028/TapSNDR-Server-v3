@php
    $pageId = 'tapsndr-transactions';
@endphp

@extends('web.parts.layout')

@push('styles')
    <link
        href="{{ URL::asset('assets/web/css/pages/master_admin/transactions.css') }}"
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
                <h1 class="text-gray-900 fw-bold my-1 fs-2">Balance History</h1>
            </div>
        </div>
    </div>
    <div class="d-flex flex-column-fluid">
        <div class="w-100 mx-auto px-10">
            <div class="row g-0">
                <div class="card">
                    <div
                        class="card-header border-0 pt-5 pb-3"
                        data-select2-id="select2-data-195-3y0d"
                    >
                        <div class="card-title w-100 w-sm-unset me-0 me-sm-2 mb-0 mb-xl-2">
                            <div class="d-flex align-items-center position-relative my-1 w-100 w-sm-unset">
                                <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <input
                                    type="text"
                                    class="form-control form-control-solid w-100 w-sm-250px ps-12 {{ $pageId }}-control-search_key"
                                    name="search_key"
                                    placeholder="Search"
                                >
                            </div>
                        </div>
                        <div class="card-toolbar mt-0 mt-xl-2">
                            <div class="pe-6 my-1">
                                <select
                                    class="form-select form-select-solid {{ $pageId }}-control-type"
                                    name="type"
                                >
                                    <option value="">All Types</option>
                                    <option value="credit">Credit</option>
                                    <option value="debit">Debit</option>
                                    <option value="deposit">Deposit</option>
                                    <option value="withdraw">Withdraw</option>
                                </select>
                            </div>
                            <div class="pe-6 my-1">
                                <select
                                    class="form-select form-select-solid w-200px {{ $pageId }}-control-user_id"
                                    name="user_id"
                                >
                                    <option value="">All Users</option>
                                </select>
                            </div>
                            <div class="pe-6 my-1">
                                <input
                                    class="form-control form-control-solid w-250px {{ $pageId }}-control-daterange"
                                    placeholder="Select date range"
                                />
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <table class="table table-striped table-row-bordered gy-5 gs-7 {{ $pageId }}-table">
                            <thead>
                                <tr>
                                    <th>Created At</th>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Ticket Amount</th>
                                    <th>Transaction Hash</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr class="fw-bold fs-5">
                                    <th
                                        colspan="3"
                                        class="text-nowrap align-end !text-start"
                                    >
                                        Total:
                                    </th>
                                    <th>
                                        <div class="spinner-border spinner-border-sm text-dark"></div>
                                    </th>
                                    <th></th>
                                    <th>
                                        <div class="spinner-border spinner-border-sm text-dark"></div>
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
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
        src="{{ URL::asset('assets/web/plugins/jquery-validation-1.19.5/jquery.validate.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/jquery-validation-1.19.5/additional-methods.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/pages/master_admin/transactions.js') }}"
    ></script>
@endpush
