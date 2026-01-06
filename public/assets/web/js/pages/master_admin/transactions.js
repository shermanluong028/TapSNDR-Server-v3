window.TapSNDRPage = (() => {
    const pageId = "tapsndr-transactions";

    const selectors = {
        table: {
            self: "." + pageId + "-table",
            buttons: {
                copyTxHash: "." + pageId + "-btn-copy_txhash",
            },
        },
        search: {
            controls: {
                search_key: "." + pageId + "-control-search_key",
                type: "." + pageId + "-control-type",
                user_id: "." + pageId + "-control-user_id",
                daterange: "." + pageId + "-control-daterange",
            },
        },
    };

    const states = {
        users: null,
        transactions: null,
        searchParams: {
            start_date: moment().startOf("day").format(),
            end_date: moment().endOf("day").format(),
        },
    };
    const ref = {
        transactionsTable: null,
        transactions: null,
        originalSearchParams: null,
    };

    const getUsers = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/users", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const getTransactions = (searchParams, cb) => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/transactions",
            {
                ...searchParams,
                orderField: "created_at",
                orderDirection: "desc",
                with: ["user", "ticket"],
            },
            (success, data, error) => {
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                cb(data);
            }
        );
    };

    const getStats = (cb) => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/transactions/stats",
            {
                ...states.searchParams,
            },
            (success, data, error) => {
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                cb(data);
            }
        );
    };

    const loadStats = () => {
        $(ref.transactionsTable.columns([3, 5]).footer()).html('<span class="spinner-border spinner-border-sm text-dark"></span>');
        getStats((data) => {
            $(ref.transactionsTable.column(3).footer()).html(
                '<span class="' +
                    (data.amount.total >= 0 ? "text-success" : "text-danger") +
                    '">' +
                    accounting.formatMoney(Math.abs(data.amount.total)) +
                    "</span>"
            );
            $(ref.transactionsTable.column(5).footer()).html(accounting.formatMoney(data.tickets.amount.total));
            // This causes ajax loading internally, which results in duplicated data loadings.
            // ref.transactionsTable.columns.adjust().draw();
        });
    };

    const initDataTable = () => {
        const getTableData = (data) => {
            const tData = [];
            for (let i = 0; i < data.length; i++) {
                const { id, user, amount, description, ticket, transaction_type: transactionType, transaction_hash: txHash, created_at: createdAt } = data[i];
                const rData = { ...data[i] };
                rData._id = id;
                // ID
                rData.id = TapSNDRUtils.getIDHTML(id);
                // Created At
                rData.created_at = TapSNDRUtils.getDateHTML(createdAt);
                // User
                rData.user = user.username + " #" + user.id;
                // Amount
                rData.amount = TapSNDRUtils.getBalanceHTML(amount, [
                    transactionType === "deposit" || transactionType === "credit" ? "text-success" : "text-danger",
                ]);
                // Description
                rData.description = TapSNDRUtils.shortenText(description);
                // Ticket
                rData.ticket_amount = TapSNDRUtils.getBalanceHTML(ticket?.amount);
                // Transaction Hash
                if (transactionType === "deposit" && txHash) {
                    rData.transaction_hash =
                        "<div class='d-flex justify-content-center align-items-center gap-1'><span>" +
                        TapSNDRUtils.shortenTxHash(txHash) +
                        "</span><a href='javascript:void(0)' class='" +
                        pageId +
                        "-btn-copy_txhash' data-txhash='" +
                        txHash +
                        "'><i class='las la-copy fs-2'></i></a><a href='https://basescan.org/tx/" +
                        txHash +
                        "' target='_blank'><i class='las la-external-link-alt fs-2'></i></a></div>";
                } else {
                    rData.transaction_hash = "-";
                }

                tData.push(rData);
            }
            return tData;
        };
        ref.transactionsTable = $(selectors.table.self).DataTable({
            processing: true,
            serverSide: true,
            ajax: ({ start, length, draw }, callback) => {
                getTransactions(
                    {
                        ...states.searchParams,
                        pageIndex: start / length,
                        pageLength: length,
                    },
                    ({ total, data }) => {
                        ref.transactions = data;
                        callback({
                            draw,
                            recordsTotal: total,
                            recordsFiltered: total,
                            data: getTableData(data),
                        });
                    }
                );
            },
            columns: [
                { title: "Created At", data: "created_at" },
                { title: "ID", data: "id" },
                { title: "User", data: "user" },
                { title: "Amount", data: "amount" },
                { title: "Description", data: "description" },
                { title: "Ticket Amount", data: "ticket_amount" },
                {
                    title: "Transaction Hash",
                    data: "transaction_hash",
                    class: "dt-center",
                },
            ],
            fixedHeader: {
                header: false,
                footer: true,
            },
            ordering: false,
            pageLength: 50,
            lengthMenu: [
                [50, 100, 200, 500],
                [50, 100, 200, 500],
            ],
            rowCallback: (row, data) => {
                const transaction = _.find(ref.transactions, { id: data._id });
                if (transaction.is_manual === "1") {
                    $(row).addClass("bg-light-warning").children().addClass("shadow-none");
                }
            },
            drawCallback: () => {
                // KTMenu.createInstances();
                setEvents();
            },
        });
    };

    const onUsersChanged = () => {
        initUsersSelect2();
    };

    const onSearchParamsChanged = () => {
        $(selectors.search.controls.search_key).val(states.searchParams.search_key);
        $(selectors.search.controls.type).val(states.searchParams.type);
        $(selectors.search.controls.user_id).val(states.searchParams.user_id);

        ref.transactionsTable.ajax.reload();
        loadStats();
    };

    const onCopyTxHash = function () {
        const txHash = $(this).data("txhash");
        navigator.clipboard.writeText(txHash).then(() => {
            $(this).html('<i class="las la-check fs-2"></i>');
            setTimeout(() => {
                $(this).html('<i class="las la-copy fs-2"></i>');
            }, 3000);
        });
    };

    const initDateRangePicker = () => {
        $(selectors.search.controls.daterange).daterangepicker(
            {
                timePicker: true,
                timePicker24Hour: true,
                startDate: moment().startOf("day"),
                endDate: moment().endOf("day"),
                locale: {
                    format: "M/DD hh:mm A",
                    cancelLabel: "Clear",
                },
            },
            (start, end) => {
                ref.originalSearchParams = { ...states.searchParams };
                states.searchParams.start_date = start.format();
                states.searchParams.end_date = end.format();
                onSearchParamsChanged();
            }
        );
        $(selectors.search.controls.daterange).on("cancel.daterangepicker", function () {
            $(this).val("");
            states.searchParams.start_date = null;
            states.searchParams.end_date = null;
            onSearchParamsChanged();
        });
    };

    const initUsersSelect2 = () => {
        $(selectors.search.controls.user_id).html('<option value="">All Users</option>');
        for (let i = 0; i < states.users.length; i++) {
            const { id, username } = states.users[i];
            $(selectors.search.controls.user_id).append('<option value="' + id + '">' + username + " #" + id + "</option>");
        }
        $(selectors.search.controls.user_id).select2();
    };

    const setEvents = () => {
        for (const key in selectors.search.controls) {
            if (key === "daterange") {
                continue;
            }
            $(typeof selectors.search.controls[key] === "object" ? selectors.search.controls[key].self : selectors.search.controls[key])
                .off("change")
                .on("change", function () {
                    if (key === "user_id") {
                        $(this).next(".select2-container").find(".select2-selection__rendered").text($(this).find("option:selected").text());
                    }
                    ref.originalSearchParams = { ...states.searchParams };
                    states.searchParams[$(this).attr("name")] = $(this).val();
                    onSearchParamsChanged();
                });
        }
        $(selectors.table.buttons.copyTxHash).off("click").on("click", onCopyTxHash);
    };

    return {
        init: function () {
            initDateRangePicker();
            setEvents();
            getUsers((users) => {
                states.users = users;
                onUsersChanged();
            });
            initDataTable();
            loadStats();
        },
    };
})();
