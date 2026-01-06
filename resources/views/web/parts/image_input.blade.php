@php
    $partId = 'tapsndr-image_input';
@endphp

@push('styles')
    <link
        href="{{ URL::asset('assets/web/css/parts/image_input.css') }}"
        rel="stylesheet"
        type="text/css"
    />
@endpush

<div
    class="image-input image-input-empty image-input-placeholder {{ $partId }} {{ $assignedId }}"
    data-kt-image-input="true"
>
    <div class="image-input-wrapper w-125px h-125px"></div>
    <label
        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
        data-kt-image-input-action="change"
        data-bs-toggle="tooltip"
        data-bs-dismiss="click"
        title="Change image"
    >
        <i class="ki-duotone ki-pencil fs-6"><span class="path1"></span><span class="path2"></span></i>
        <input
            type="file"
            name="{{ $name }}"
            accept="{{ $accept }}"
        />
        <input
            type="hidden"
            name="image_remove"
        />
    </label>
    <span
        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
        data-kt-image-input-action="cancel"
        data-bs-toggle="tooltip"
        data-bs-dismiss="click"
        title="Cancel image"
    >
        <i class="ki-outline ki-cross fs-3"></i>
    </span>
    <span
        class="btn btn-icon btn-circle btn-color-muted btn-active-color-primary w-25px h-25px bg-body shadow"
        data-kt-image-input-action="remove"
        data-bs-toggle="tooltip"
        data-bs-dismiss="click"
        title="Remove image"
    >
        <i class="ki-outline ki-cross fs-3"></i>
    </span>
</div>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/image_input.js') }}"
    ></script>
@endpush
