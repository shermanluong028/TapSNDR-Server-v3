@php
    $partId = 'tapsndr-form-vendor_code';
@endphp

<form class="{{ $partId }} {{ $assignedId }}">
    <div class="form-group">
        <label class="form-label required">Vendor Code</label>
        <input
            class="form-control form-control-solid"
            name="vendor_code"
        />
    </div>
</form>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/forms/vendor_code.js') }}"
    ></script>
@endpush
