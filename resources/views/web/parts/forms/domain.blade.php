@php
    $partId = 'tapsndr-form-domain';
@endphp

<form class="{{ $partId }} {{ $assignedId }}">
    <input
        type="hidden"
        name="id"
    />
    <div class="mb-10 form-group">
        <label class="form-label required">Vendor Code</label>
        <input
            class="form-control form-control-solid"
            name="vendor_code"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Description</label>
        <input
            class="form-control form-control-solid"
            name="group_name"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Games</label>
        <input
            class="form-control form-control-solid"
            name="games"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Telegram Chat ID</label>
        <input
            class="form-control form-control-solid"
            name="telegram_chat_id"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label">Client</label>
        <select
            class="form-control form-control-solid"
            name="client_id"
        ></select>
    </div>
</form>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/forms/domain.js') }}"
    ></script>
@endpush
