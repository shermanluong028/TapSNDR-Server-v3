@php
    $pageId = 'tapsndr-vendors';
@endphp

@extends('web.parts.layout')

@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="w-100 mx-auto px-10">
            <div class="row g-0">
                <div class="card card-flush mb-5 mb-xl-10">
                    <div class="card-header pt-7">
                        <div class="card-title flex-column">
                            <h3 class="m-0">Vendors</h3>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">
                                The Vendor code is the same as
                                <a href="https://taparcadia.com">
                                    <img
                                        src="{{ URL::asset('assets/web/media/taparcadia.png') }}"
                                        alt="TapArcadia"
                                        width="130px"
                                    />
                                </a>
                                to submit redeems for specific game rooms.
                            </span>
                        </div>
                        <div class="card-toolbar">
                            <button
                                type="button"
                                class="btn btn-primary {{ $pageId }}-btn-add_new"
                            >
                                Add new
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row gx-9 gy-6 {{ $pageId }}-list">
                            <div class="d-flex justify-content-center">
                                <div class="w-auto p-4 rounded shadow-sm">
                                    <span class="spinner-border w-15px h-15px text-muted align-middle me-2"></span>
                                    <span class="text-gray-600">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    @include('web.parts.modals.vendor_code', ['assignedId' => $pageId . '-modal-vendor_code'])
    @include('web.parts.modals.ticket', ['assignedId' => $pageId . '-modal-ticket'])
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
        src="{{ URL::asset('assets/web/js/pages/player/vendors.js') }}"
    ></script>
@endpush
