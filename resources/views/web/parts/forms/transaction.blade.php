@php
    $partId = 'tapsndr-form-transaction';
@endphp

<form class="{{ $partId }} {{ $assignedId }}">
    <div class="mb-10 form-group">
        <label class="form-label required">Type</label>
        <select
            class="form-control form-control-solid"
            name="transaction_type"
        >
            <option value="credit">Credit</option>
            <option value="debit">Debit</option>
            <option value="deposit">Deposit</option>
            <option value="withdraw">Withdraw</option>
        </select>
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Amount</label>
        <input
            type="number"
            class="form-control form-control-solid"
            name="amount"
            min="0"
        />
    </div>
    <div class="mb-10 form-group">
        <label class="form-label required">Ticket ID</label>
        <input
            class="form-control form-control-solid"
            name="transaction_hash"
        />
    </div>
    <div class="form-group">
        <label class="form-label required">Description</label>
        <textarea
            class="form-control form-control-solid"
            name="description"
            rows="5"
        ></textarea>
    </div>
</form>

@push('scripts')
    <script
        type="text/javascript"
        src="{{ URL::asset('assets/web/js/parts/forms/transaction.js') }}"
    ></script>
@endpush
