@php
    $partId = 'tapsndr-modal-private_key';
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
                <h3 class="modal-title">Private Key</h3>
                <div
                    class="btn btn-icon btn-sm btn-active-light-primary ms-2"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                >
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group mb-10">
                        <input
                            class="form-control form-control-solid"
                            name="private_key"
                        />
                    </div>
                    <div class="separator separator-content my-15"><span class="w-250px fw-bold">Setup New Secret Key</span></div>
                    <div class="form-group mb-10">
                        <label class="form-label required">Secret Key</label>
                        <input
                            type="password"
                            class="form-control form-control-solid"
                            name="secret_key"
                            placeholder="******"
                        />
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Confirm Secret Key</label>
                        <input
                            type="password"
                            class="form-control form-control-solid"
                            name="secret_key1"
                            placeholder="******"
                        />
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary {{ $partId }}-btn-submit">
                    Save
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/modals/private_key.js') }}"
    ></script>
@endpush
