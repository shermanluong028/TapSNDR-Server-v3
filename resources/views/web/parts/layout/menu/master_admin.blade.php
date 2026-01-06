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
                class="d-flex justify-content-between align-items-center border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 mb-3 {{ $partId }}-balance">
                <div>
                    <div class="d-flex align-items-center">
                        <div
                            class="fs-2 fw-bold counted"
                            {{-- data-kt-countup="true" data-kt-countup-value="4500"
                            data-kt-countup-prefix="$" data-kt-initialized="1" --}}
                        >
                            <div class="spinner-border w-20px h-20px"></div>
                        </div>
                    </div>
                    <div class="fw-semibold fs-6 text-gray-500">Balance</div>
                </div>
                <button class="btn btn-icon btn-light">
                    <i class="las la-hand-holding-usd fs-2"></i>
                </button>
            </div>
            <div
                data-kt-menu-trigger="click"
                @class([
                    'menu-item',
                    'here show' => in_array(request()->path(), [
                        'dashboard/wallets',
                        'dashboard/tickets',
                    ]),
                    'menu-accordion',
                ])
            >
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="las la-th-large fs-2"></i>
                    </span>
                    <span class="menu-title">Dashboards</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion">
                    <div class="menu-item">
                        <a
                            @class([
                                'menu-link',
                                'active' => request()->path() === 'dashboard/tickets',
                            ])
                            href="{{ url('/dashboard/tickets') }}"
                        >
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Tickets</span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="menu-item pt-5">
                <div class="menu-content">
                    <span class="fw-bold text-muted text-uppercase fs-7">Management</span>
                </div>
            </div>
            <div
                data-kt-menu-trigger="click"
                class="menu-item {{ $partId }}-item-accounts"
            >
                <a
                    @class(['menu-link', 'active' => request()->path() === 'accounts'])
                    href="{{ url('/accounts') }}"
                >
                    <span class="menu-icon">
                        <i class="las la-users fs-2"></i>
                    </span>
                    <span class="menu-title">Accounts</span>
                </a>
            </div>
            <div
                data-kt-menu-trigger="click"
                class="menu-item {{ $partId }}-item-domains"
            >
                <a
                    @class(['menu-link', 'active' => request()->path() === 'domains'])
                    href="{{ url('/domains') }}"
                >
                    <span class="menu-icon">
                        <i class="las la-user-tie fs-2"></i>
                    </span>
                    <span class="menu-title">Vendors</span>
                </a>
            </div>
            <div class="menu-item pt-5">
                <div class="menu-content">
                    <span class="fw-bold text-muted text-uppercase fs-7">Transactions</span>
                </div>
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
                    <span class="menu-badge d-none">
                        <span class="badge badge-warning"></span>
                    </span>
                </a>
            </div>
            <div
                data-kt-menu-trigger="click"
                class="menu-item {{ $partId }}-item-transactions"
            >
                <a
                    @class([
                        'menu-link',
                        'active' => request()->path() === 'transactions',
                    ])
                    href="{{ url('/transactions') }}"
                >
                    <span class="menu-icon">
                        <i class="las la-exchange-alt fs-2"></i>
                    </span>
                    <span class="menu-title">Balance History</span>
                    <span class="menu-badge d-none">
                        <span class="badge badge-warning"></span>
                    </span>
                </a>
            </div>
            <div class="menu-item pt-5">
                <div class="menu-content">
                    <span class="fw-bold text-muted text-uppercase fs-7">Financial</span>
                </div>
            </div>
            <div
                data-kt-menu-trigger="click"
                class="menu-item {{ $partId }}-item-withdrawals"
            >
                <a
                    @class(['menu-link', 'active' => request()->path() === 'withdrawals'])
                    href="{{ url('/withdrawals') }}"
                >
                    <span class="menu-icon">
                        <i class="las la-hand-holding-usd fs-2"></i>
                    </span>
                    <span class="menu-title">Withdrawals</span>
                    <span class="menu-badge d-none">
                        <span class="badge badge-warning"></span>
                    </span>
                </a>
            </div>
            <div class="menu-item pt-5">
                <div class="menu-content">
                    <span class="fw-bold text-muted text-uppercase fs-7">Settings</span>
                </div>
            </div>
            <div
                data-kt-menu-trigger="click"
                class="menu-item {{ $partId }}-item-settings-wallet"
            >
                <a
                    @class([
                        'menu-link',
                        'active' => request()->path() === 'settings/wallet',
                    ])
                    href="{{ url('/settings/wallet') }}"
                >
                    <span class="menu-icon">
                        <i class="las la-coins fs-2"></i>
                    </span>
                    <span class="menu-title">Wallet</span>
                    <span class="menu-badge d-none">
                        <span class="badge badge-warning"></span>
                    </span>
                </a>
            </div>
            <div
                data-kt-menu-trigger="click"
                @class([
                    'menu-item',
                    'here show' => in_array(request()->path(), [
                        'settings/ticket/payment_methods',
                    ]),
                    'menu-accordion',
                ])
            >
                <span class="menu-link">
                    <span class="menu-icon">
                        <i class="las la-ticket-alt fs-2"></i>
                    </span>
                    <span class="menu-title">Ticket</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion">
                    <div class="menu-item">
                        <a
                            @class([
                                'menu-link',
                                'active' => request()->path() === 'settings/ticket/payment_methods',
                            ])
                            href="{{ url('/settings/ticket/payment_methods') }}"
                        >
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">Payment Methods</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/menu/master_admin.js') }}"
    ></script>
@endpush
