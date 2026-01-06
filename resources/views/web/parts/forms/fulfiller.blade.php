@php
    $partId = 'tapsndr-form-fulfiller';
@endphp

<form class="{{ $partId }} {{ $assignedId }}">
    <input
        type="hidden"
        name="id"
    />
    <div class="form-group">
        <label class="form-label required">Fulfiller</label>
        <select
            class="form-control form-control-solid"
            name="user_id"
        ></select>
    </div>
</form>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/forms/fulfiller.js') }}"
    ></script>
@endpush
