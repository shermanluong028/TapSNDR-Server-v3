@php
    $partId = 'tapsndr-stats-client';
@endphp

<div class="d-flex flex-wrap {{ $partId }} {{ $assignedId }}">
    <div class="border border-dashed border-gray-300 rounded my-3 p-4 me-6 {{ $partId }}-tickets-amount-completed">
        <span class="fs-2x fw-bold text-gray-800 lh-1">
            <span
                data-kt-countup="true"
                data-kt-countup-value="6,840"
                data-kt-countup-prefix="$"
                class="counted"
                data-kt-initialized="1"
            ></span>
        </span>
        <span class="fs-6 fw-semibold text-gray-500 d-block lh-1 pt-2">Total amount of completed tickets</span>
    </div>

    <div class="border border-dashed border-gray-300 rounded my-3 p-4 me-6 {{ $partId }}-tickets-fee">
        <span class="fs-2x fw-bold text-gray-800 lh-1">
            <span
                data-kt-countup="true"
                data-kt-countup-value="6,840"
                data-kt-countup-prefix="$"
                class="counted"
                data-kt-initialized="1"
            ></span>
        </span>
        <span class="fs-6 fw-semibold text-gray-500 d-block lh-1 pt-2">Profit from this client</span>
    </div>

    <div class="border border-dashed border-gray-300 rounded my-3 p-4 me-6 {{ $partId }}-tickets-count-total">
        <span class="fs-2x fw-bold text-gray-800 lh-1">
            <span
                data-kt-countup="true"
                data-kt-countup-value="6,840"
                data-kt-countup-prefix="$"
                class="counted"
                data-kt-initialized="1"
            ></span>
        </span>
        <span class="fs-6 fw-semibold text-gray-500 d-block lh-1 pt-2">Tickets submitted</span>
    </div>

    <div class="border border-dashed border-gray-300 rounded my-3 p-4 me-6 {{ $partId }}-tickets-amount-avg">
        <span class="fs-2x fw-bold text-gray-800 lh-1">
            <span
                data-kt-countup="true"
                data-kt-countup-value="6,840"
                data-kt-countup-prefix="$"
                class="counted"
                data-kt-initialized="1"
            ></span>
        </span>
        <span class="fs-6 fw-semibold text-gray-500 d-block lh-1 pt-2">Avg ticket amount</span>
    </div>
</div>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/stats/client.js') }}"
    ></script>
@endpush
