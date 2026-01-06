@php
    $partId = 'tapsndr-drawer-domain';
@endphp

<div
    class="bg-body {{ $partId }} {{ $assignedId }}"
    data-kt-drawer="true"
    data-kt-drawer-name="tournament"
    data-kt-drawer-activate="true"
    data-kt-drawer-overlay="true"
    data-kt-drawer-width="{default:'100%', 'sm': '450px'}"
    data-kt-drawer-direction="end"
    data-kt-drawer-close=".{{ $partId }}.{{ $assignedId }} .{{ $partId }}-close"
>
    <div class="card shadow-none border-0 rounded-0 w-100">
        <div class="card-header">
            <h3 class="card-title fw-bold text-gray-900"></h3>
            <div class="card-toolbar">
                <button
                    type="button"
                    class="btn btn-sm btn-icon btn-active-light-primary me-n5 {{ $partId }}-close"
                >
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </button>
            </div>
        </div>
        <div class="card-body position-relative">
            <div
                class="position-relative scroll-y me-n5 pe-5"
                data-kt-scroll="true"
                data-kt-scroll-height="auto"
                data-kt-scroll-wrappers=".{{ $partId }}.{{ $assignedId }} .card-body"
                data-kt-scroll-dependencies=".{{ $partId }}.{{ $assignedId }} .card-header, .{{ $partId }}.{{ $assignedId }} .card-footer"
                data-kt-scroll-offset="5px"
            >
                @include('web.parts.forms.domain', ['assignedId' => $partId . '-' . $assignedId . '-form'])
            </div>
        </div>
        <div class="card-footer py-5 text-center">
            <button class="btn btn-bg-body text-primary {{ $partId }}-submit">Submit</button>
        </div>
    </div>
</div>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/drawers/domain.js') }}"
    ></script>
@endpush
