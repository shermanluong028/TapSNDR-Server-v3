@php
    $partId = 'tapsndr-form-withdrawal';
@endphp

<form class="{{ $partId }} {{ $assignedId }}">
    @if ($currentUser->role === 'master_admin')
        <div class="mb-10 form-group">
            <label class="form-label required">For TapSNDR From Client</label>
            <input
                type="number"
                class="form-control form-control-solid"
                name="admin_client"
            />
        </div>
        <div class="mb-10 form-group">
            <label class="form-label required">For TapSNDR From Customer</label>
            <input
                type="number"
                class="form-control form-control-solid"
                name="admin_customer"
            />
        </div>
    @endif
    <div class="mb-10 form-group">
        <label class="form-label required">For Distributor From Client</label>
        <input
            type="number"
            class="form-control form-control-solid"
            name="distributor_client"
        />
    </div>
    <div class="form-group">
        <label class="form-label required">For Distributor From Customer</label>
        <input
            type="number"
            class="form-control form-control-solid"
            name="distributor_customer"
        />
    </div>
</form>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/forms/withdrawal.js') }}"
    ></script>
@endpush
