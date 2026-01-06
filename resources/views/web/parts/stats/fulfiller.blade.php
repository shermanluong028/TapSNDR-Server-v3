@php
    $partId = 'tapsndr-stats-fulfiller';
@endphp

<div class="d-flex flex-wrap {{ $partId }} {{ $assignedId }}">
    <div class="border border-dashed border-gray-300 rounded my-3 p-4 me-6 {{ $partId }}-tickets-amount-avg-1hour">
        <span class="fs-2x fw-bold text-gray-800 lh-1">
            <span
                data-kt-countup="true"
                data-kt-countup-value="6,840"
                data-kt-countup-prefix="$"
                class="counted"
                data-kt-initialized="1"
            ></span>
        </span>
        <span class="fs-6 fw-semibold text-gray-500 d-block lh-1 pt-2">Avg tickets processed in 1 hour</span>
    </div>

    <div class="border border-dashed border-gray-300 rounded my-3 p-4 me-6 {{ $partId }}-tickets-count-completed">
        <span class="fs-2x fw-bold text-gray-800 lh-1">
            <span
                data-kt-countup="true"
                data-kt-countup-value="6,840"
                data-kt-countup-prefix="$"
                class="counted"
                data-kt-initialized="1"
            ></span>
        </span>
        <span class="fs-6 fw-semibold text-gray-500 d-block lh-1 pt-2">Tickets processed</span>
    </div>

    <div class="border border-dashed border-gray-300 rounded my-3 p-4 me-6 {{ $partId }}-tickets-count-reported">
        <span class="fs-2x fw-bold text-gray-800 lh-1">
            <span
                data-kt-countup="true"
                data-kt-countup-value="6,840"
                data-kt-countup-prefix="$"
                class="counted"
                data-kt-initialized="1"
            ></span>
        </span>
        <span class="fs-6 fw-semibold text-gray-500 d-block lh-1 pt-2">Tickets reported</span>
    </div>
</div>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/stats/fulfiller.js') }}"
    ></script>
@endpush
