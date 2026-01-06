@php
    $partId = 'tapsndr-menu';
@endphp

<div class="aside-menu flex-column-fluid ps-3 pe-1">
    <div
        class="menu menu-sub-indention menu-column menu-rounded menu-title-gray-600 menu-icon-gray-500 menu-active-bg menu-state-primary menu-arrow-gray-500 fw-semibold fs-6 my-5 mt-lg-2 mb-lg-0"
        id="kt_aside_menu"
        data-kt-menu="true"
    >
        <div
            class="hover-scroll-y mx-4"
            id="kt_aside_menu_wrapper"
            data-kt-scroll="true"
            data-kt-scroll-activate="{default: false, lg: true}"
            data-kt-scroll-height="auto"
            data-kt-scroll-wrappers="#kt_aside_menu"
            data-kt-scroll-offset="20px"
            data-kt-scroll-dependencies="#kt_aside_logo, #kt_aside_footer"
        >
            <div
                data-kt-menu-trigger="click"
                class="menu-item {{ $partId }}-item-payment_details"
            >
                <a
                    @class([
                        'menu-link',
                        'active' => request()->path() === 'player/payment_details',
                    ])
                    href="{{ url('/player/payment_details') }}"
                >
                    <span class="menu-icon">
                        <i class="las la-credit-card fs-2"></i>
                    </span>
                    <span class="menu-title">Payment Methods</span>
                </a>
            </div>
            <div
                data-kt-menu-trigger="click"
                class="menu-item {{ $partId }}-item-payment_details"
            >
                <a
                    @class([
                        'menu-link',
                        'active' => request()->path() === 'player/vendors',
                    ])
                    href="{{ url('/player/vendors') }}"
                >
                    <span class="menu-icon">
                        <i class="las la-user-tie fs-2"></i>
                    </span>
                    <span class="menu-title">Vendors</span>
                </a>
            </div>
            <div
                data-kt-menu-trigger="click"
                class="menu-item {{ $partId }}-item-tickets"
            >
                <a
                    @class(['menu-link', 'active' => request()->path() === 'tickets'])
                    href="{{ url('/tickets') }}"
                >
                    <span class="menu-icon">
                        <i class="las la-ticket-alt fs-2"></i>
                    </span>
                    <span class="menu-title">Tickets</span>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/menu/player.js') }}"
    ></script>
@endpush
