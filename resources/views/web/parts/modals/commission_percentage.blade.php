@php
    $partId = 'tapsndr-modal-commission_percentage';
@endphp

<div
    class="modal fade {{ $partId }} {{ $assignedId }}"
    {{-- data-bs-backdrop="static" --}}
    {{-- data-bs-keyboard="false" --}}
    tabindex="-1"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"></h3>
                <div
                    class="btn btn-icon btn-sm btn-active-light-primary ms-2"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body">
                @include('web.parts.forms.commission_percentage', [
                    'assignedId' => $partId . '-' . $assignedId . '-form',
                ])
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary {{ $partId }}-btn-submit">
                    Submit
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/modals/commission_percentage.js') }}"
    ></script>
@endpush
