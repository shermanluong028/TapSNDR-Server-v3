@php
    $partId = 'tapsndr-form-refund';
@endphp

<form class="{{ $partId }} {{ $assignedId }}">
    <div class="row mb-7 {{ $partId }}-ticket-ticket_id">
        <label class="col-lg-4 fw-semibold text-muted">Ticket ID</label>
        <div class="col-lg-8">
            <span class="fw-bold fs-6 text-gray-800"></span>
        </div>
    </div>
    <div class="row mb-7 {{ $partId }}-ticket-amount">
        <label class="col-lg-4 fw-semibold text-muted">Amount</label>
        <div class="col-lg-8 fv-row">
            <span class="fw-semibold text-gray-800 fs-6"></span>
        </div>
    </div>
    <div class="row mb-7 {{ $partId }}-ticket-client">
        <label class="col-lg-4 fw-semibold text-muted">Client</label>
        <div class="col-lg-8 fv-row">
            <span class="fw-semibold text-gray-800 fs-6"></span>
        </div>
    </div>
    <div class="row mb-7 {{ $partId }}-ticket-customer">
        <label class="col-lg-4 fw-semibold text-muted">Customer</label>
        <div class="col-lg-8">
            <span class="fw-semibold text-gray-800 fs-6"></span>
        </div>
    </div>
    <div class="row mb-7 {{ $partId }}-ticket-fulfiller">
        <label class="col-lg-4 fw-semibold text-muted">Fulfiller</label>
        <div class="col-lg-8">
            <span class="fw-semibold text-gray-800 fs-6"></span>
        </div>
    </div>
    <div class="row mb-7 {{ $partId }}-ticket-created_at">
        <label class="col-lg-4 fw-semibold text-muted">Created At</label>
        <div class="col-lg-8">
            <span class="fw-semibold text-gray-800 fs-6"></span>
        </div>
    </div>
    <div class="row mb-7 {{ $partId }}-ticket-completed_at">
        <label class="col-lg-4 fw-semibold text-muted">Completed At</label>
        <div class="col-lg-8">
            <span class="fw-semibold text-gray-800 fs-6"></span>
        </div>
    </div>
    <div class="mb-10 form-group">
        <label class="d-flex align-items-center form-label">
            <span class="required">Amount</span>
            <span
                class="ms-1"
                data-bs-toggle="tooltip"
                title="Enter the amount that the fulfiller was unable to transfer"
            >
                <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
            </span>
        </label>
        <input
            type="number"
            class="form-control form-control-solid"
            name="amount"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Inconvenience Fee</label>
        <div class="input-group input-group-solid">
            <input
                type="text"
                class="form-control"
                name="inconvenience_fee"
            />
            <span class="input-group-text cursor-pointer {{ $partId }}-inconvenience_fee-type">%</span>
            <input
                type="text"
                class="form-control"
                name="inconvenience_fee_result"
                disabled
            />
        </div>
    </div>
</form>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/forms/refund.js') }}"
    ></script>
@endpush
