@php
    $partId = 'tapsndr-form-payment_details';
@endphp

<form class="{{ $partId }} {{ $assignedId }}">
    <div
        class="row mb-10 {{ $partId }}-payment_methods"
        data-kt-buttons="true"
        data-kt-buttons-target=".form-check-image, .form-check-input"
    >
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">@</label>
        <input
            class="form-control form-control-solid"
            name="tag"
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
        <label class="form-label required">Phone number</label>
        <input
            class="form-control form-control-solid"
            name="phone_number"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Email or Phone number</label>
        <input
            class="form-control form-control-solid"
            name="email_or_phone"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Name on Account</label>
        <input
            class="form-control form-control-solid"
            name="account_name"
        />
    </div>
    <div class="form-group">
        <label class="form-label required">Upload QR Code image</label>
        <div>
            @include('web.parts.image_input', [
                'assignedId' => $partId . '-' . $assignedId . '-qrcode',
                'name' => 'qrcode',
                'accept' => 'image/png, image/jpg, image/jpeg',
            ])
        </div>
    </div>
</form>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/forms/payment_details.js') }}"
    ></script>
@endpush
