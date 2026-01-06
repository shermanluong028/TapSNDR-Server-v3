@php
    $pageId = 'tapsndr-tickets';
@endphp

@extends('web.parts.layout')

@push('styles')
    <link
        href="{{ URL::asset('assets/web/plugins/datatables/datatables.bundle.css') }}"
        rel="stylesheet"
        type="text/css"
    />
@endpush

@section('content')
    <div
        class="toolbar"
        id="kt_toolbar"
    >
        <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
            <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                <h1 class="text-gray-900 fw-bold my-1 fs-2">
                    Tickets
                    <i class="ki-duotone ki-arrows-circle fs-4 ms-1 cursor-pointer {{ $pageId }}-icon-refresh">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </h1>
            </div>
        </div>
    </div>
    <div class="d-flex flex-column-fluid">
        <div class="w-100 mx-auto px-10">
            <div class="row g-0">
                @include('web.parts.tables.tickets.' . $currentUser->role, [
                    'assignedId' => $pageId . '-table',
                ])
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/plugins/datatables/datatables.bundle.js') }}"
    ></script>

    @if ($currentUser->role === 'master_admin' || $currentUser->role === 'user')
        <script
            type="text/javascript"
            src="{{ URL::asset('assets/web/plugins/jquery-validation-1.19.5/jquery.validate.js') }}"
        ></script>
        <script
            type="text/javascript"
            src="{{ URL::asset('assets/web/plugins/jquery-validation-1.19.5/additional-methods.js') }}"
        ></script>
    @endif

    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/pages/' . $currentUser->role . '/tickets.js') }}"
    ></script>
@endpush
