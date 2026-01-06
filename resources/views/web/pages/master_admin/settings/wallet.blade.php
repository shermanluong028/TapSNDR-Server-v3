@php
    $pageId = 'tapsndr-settings-wallet';
@endphp

@extends('web.parts.layout')

@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="w-100 mx-auto px-10">
            <div class="d-flex flex-column flex-lg-row">
                <div class="flex-md-row-fluid">
                    <div class="card mb-5 mb-xl-10">
                        <div
                            class="card-header border-0 cursor-pointer"
                            role="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#{{ $pageId }}-wallet"
                            aria-expanded="true"
                            aria-controls="{{ $pageId }}-wallet"
                        >
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Wallet</h3>
                            </div>
                        </div>
                        <div class="collapse show {{ $pageId }}-wallet">
                            <form class="form">
                                <div class="card-body border-top p-9">
                                    <div class="d-flex mb-6">
                                        <label class="col-form-label fw-semibold fs-6 me-5">
                                            <span class="required">Address</span>
                                        </label>
                                        <div class="flex-fill fv-row">
                                            <input
                                                name="address"
                                                class="form-control form-control-lg form-control-solid"
                                            />
                                        </div>
                                    </div>
                                    <div
                                        class="notice d-flex bg-light-danger rounded border-danger border border-dashed p-6">
                                        <i class="ki-duotone ki-shield-tick fs-2tx text-danger me-4">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="d-flex flex-stack flex-grow-1 flex-wrap flex-md-nowrap">
                                            <div class="mb-3 mb-md-0 fw-semibold">
                                                <h4 class="text-gray-900 fw-bold">Secure Wallet</h4>
                                                <div class="fs-6 text-gray-700 pe-7">Never disclose this key. Anyone with
                                                    your private key can access your wallet.</div>
                                            </div>
                                            <button
                                                type="button"
                                                class="btn btn-danger px-6 align-self-center text-nowrap {{ $pageId }}-wallet-btn-show_private_key"
                                            >
                                                Show Private Key
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer d-flex justify-content-end py-6 px-9">
                                    <button
                                        type="submit"
                                        class="btn btn-primary"
                                    >
                                        Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    @include('web.parts.modals.secret_key', ['assignedId' => $pageId . '-modal-secret_key'])
    @include('web.parts.modals.private_key', ['assignedId' => $pageId . '-modal-private_key'])
@endpush

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/jquery-validation-1.19.5/jquery.validate.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/jquery-validation-1.19.5/additional-methods.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/crypto-js-4.2.0/package/crypto-js.js') }}"
    ></script>
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/pages/master_admin/settings/wallet.js') }}"
    ></script>
@endpush
