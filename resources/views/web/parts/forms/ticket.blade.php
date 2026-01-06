@php
    $partId = 'tapsndr-form-ticket';
@endphp

<form class="{{ $partId }} {{ $assignedId }}">
    <div class="alert alert-dismissible bg-light-warning d-flex align-items-center p-5 fs-6 mb-5">
        <i class="ki-duotone ki-information fs-2hx text-warning me-4">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
        <div class="d-flex flex-column text-dark">
            <span>
                TapSNDR redeems cost
                <span class="{{ $partId }}-commission_percentage me-[-3px]">
                    <span class="ssc-square w-20px h-20px d-inline-block align-bottom"></span>
                </span>
                %! DO NOT SEND BACK
                TO WHERE YOU RECEIVE FROM! WE WILL NOT BE HELD RESPONSIBLE FOR ANY PAYMENTS SENT TO ACCOUNTS FROM TAP!
                Payments will be sent within 1-3 hours.
            </span>
        </div>
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Facebook Name</label>
        <input
            class="form-control form-control-solid"
            name="facebook_name"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Amount (Minimum 100)</label>
        <input
            type="number"
            class="form-control form-control-solid"
            name="amount"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Game</label>
        <select
            class="form-control form-control-solid"
            name="game"
        ></select>
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Game ID</label>
        <input
            class="form-control form-control-solid"
            name="game_id"
        />
    </div>
    {{-- <div class="form-group">
        <label class="form-label required">Payment Method</label>
        <div class="form-control-solid-bg rounded">
            <select
                class="form-select form-select-transparent"
                name="payment_details_id"
            ></select>
        </div>
    </div> --}}
</form>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/forms/ticket.js') }}"
    ></script>
@endpush
