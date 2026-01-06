@php
    $partId = 'tapsndr-table-tickets';
@endphp

@push('styles')
    <link
        href="{{ URL::asset('assets/web/css/parts/tables/tickets.css') }}"
        rel="stylesheet"
        type="text/css"
    />
@endpush

<div class="{{ $partId }} {{ $assignedId }}">
    <div class="card card-flush">
        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
            <div class="card-title w-100 w-xl-unset me-0 me-xl-2">
                <div class="d-flex align-items-center position-relative my-1 w-100 w-sm-unset">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input
                        type="text"
                        class="form-control form-control-solid w-100 w-sm-250px ps-12 {{ $partId }}-control-search_key"
                        placeholder="Search Ticket"
                    >
                </div>
            </div>
            <div
                class="card-toolbar flex-row-fluid flex-column flex-xl-row justify-content-center justify-content-xl-end align-items-start align-items-xl-center gap-5">
                <div class="w-[150px] pe-6 my-1">
                    <select
                        data-control="select2"
                        data-hide-search="true"
                        class="form-select form-select-solid {{ $partId }}-control-status"
                    >
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="validated">Validated</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="reported">Reported</option>
                        <option value="declined">Declined</option>
                        <option value="error">Error</option>
                    </select>
                </div>
                <div class="pe-6 my-1">
                    <input
                        class="form-control form-control-solid w-250px {{ $partId }}-control-daterange"
                        placeholder="Select date range"
                    />
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <table class="table table-striped table-row-bordered gy-5 gs-7"></table>
        </div>
    </div>
</div>

@push('modals')
    @include('web.parts.modals.payment_details', [
        'assignedId' => $partId . '-' . $assignedId . '-modal-payment_details',
    ])
@endpush

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/tables/tickets/user.js') }}"
    ></script>
@endpush
