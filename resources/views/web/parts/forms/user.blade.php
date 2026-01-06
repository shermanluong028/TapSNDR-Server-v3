@php
    $partId = 'tapsndr-form-user';
@endphp

<form class="{{ $partId }} {{ $assignedId }}">
    <input
        type="hidden"
        name="id"
    />
    <div class="mb-10 form-group">
        <label class="form-label required">Username</label>
        <input
            class="form-control form-control-solid"
            name="username"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Email</label>
        <input
            class="form-control form-control-solid"
            name="email"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label">Phone</label>
        <input
            class="form-control form-control-solid"
            name="phone"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Role</label>
        <select
            class="form-control form-control-solid"
            name="role"
        ></select>
    </div>
    {{-- <div class="mb-10 form-group">
        <label class="form-label required">Domains</label>
        <input
            class="form-control form-control-solid"
            name="domains"
        />
    </div> --}}
    <div class="mb-10 form-group">
        <label class="form-label required">Password</label>
        <input
            type="password"
            class="form-control form-control-solid"
            name="password"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Confirm Password</label>
        <input
            type="password"
            class="form-control form-control-solid"
            name="password1"
        />
    </div>
    {{-- <div class="{{ $partId }}-commission_percentage">
        <div class="separator separator-content my-15">Commission Percentage</div>
        <div class="mb-10 form-group">
            <label class="form-label required">For TapSNDR From Client</label>
            <input
                type="number"
                class="form-control form-control-solid"
                name="admin_client_commission_percentage"
            />
        </div>
        <div class="mb-10 form-group">
            <label class="form-label required">For TapSNDR From Customer</label>
            <input
                type="number"
                class="form-control form-control-solid"
                name="admin_customer_commission_percentage"
            />
        </div>
        <div class="mb-10 form-group">
            <label class="form-label required">For Distributor From Client</label>
            <input
                type="number"
                class="form-control form-control-solid"
                name="distributor_client_commission_percentage"
            />
        </div>
        <div class="mb-10 form-group">
            <label class="form-label required">For Distributor From Customer</label>
            <input
                type="number"
                class="form-control form-control-solid"
                name="distributor_customer_commission_percentage"
            />
        </div>
    </div> --}}
</form>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/forms/user.js') }}"
    ></script>
@endpush
