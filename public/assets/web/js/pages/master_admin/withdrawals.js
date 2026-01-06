window.TapSNDRPage = (() => {
    const pageId = "tapsndr-withdrawals";

    const selectors = {
        table: "." + pageId + "-table",
        buttons: {
            copyTxHash: "." + pageId + "-btn-copy_txhash",
            approve: "." + pageId + "-btn-approve",
            decline: "." + pageId + "-btn-decline",
        },
    };

    // const states = {};
    const ref = {
        withdrawalsTable: null,
        withdrawals: null,
        secretKeyModal: null,
    };

    const getWithdrawals = (searchParams, cb) => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/withdrawals",
            {
                ...searchParams,
                orderField: "created_at",
                orderDirection: "desc",
                with: ["user", "transaction"],
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

    const initDataTable = () => {
        const getTableData = (data) => {
            const mapBadgeToStatus = {
                PENDING: "<span class='badge badge-light-warning'>Pending</span>",
                APPROVED: "<span class='badge badge-light-success'>Approved</span>",
                REPORT: "<span class='badge badge-light-danger'>Declined</span>",
                FAILED: "<span class='badge badge-light-danger'>Failed</span>",
            };
            const tData = [];
            for (let i = 0; i < data.length; i++) {
                const rData = { ...data[i] };
                rData._id = data[i].id;
                rData.id = TapSNDRUtils.getIDHTML(data[i].id);
                rData.created_at = TapSNDRUtils.getDateHTML(data[i].created_at);
                rData.user = data[i].user ? data[i].user.username + " #" + data[i].user.id : "-";
                rData.amount = TapSNDRUtils.getBalanceHTML(data[i].amount);
                rData.status = mapBadgeToStatus[data[i].status];

                // Transaction Hash
                const txHash = data[i].transaction?.transaction_hash;
                if (txHash) {
                    rData.transaction_hash =
                        "<div class='d-flex justify-content-center align-items-center gap-1'><span>" +
                        TapSNDRUtils.shortenTxHash(txHash) +
                        "</span><a href='javasciprt:void(0)' class='" +
                        pageId +
                        "-btn-copy_txhash' data-txhash='" +
                        txHash +
                        "'><i class='las la-copy fs-2'></i></a><a href='https://basescan.org/tx/" +
                        txHash +
                        "' target='_blank'><i class='las la-external-link-alt fs-2'></i></a></div>";
                } else {
                    rData.transaction_hash = "-";
                }

                rData.actions =
                    '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions <i class="ki-duotone ki-down fs-5 ms-1"></i></a>';
                rData.actions +=
                    '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-3" data-kt-menu="true">';

                if (data[i].status === "PENDING") {
                    // Actions - Approve
                    rData.actions += '<div class="menu-item px-3">';
                    rData.actions += '<a href="javascript:void(0)" class="menu-link px-3 ' + pageId + '-btn-approve" data-id="' + data[i].id + '">Approve</a>';
                    rData.actions += "</div>";

                    // Actions - Decline
                    rData.actions += '<div class="menu-item px-3">';
                    rData.actions += '<a href="javascript:void(0)" class="menu-link px-3 ' + pageId + '-btn-decline" data-id="' + data[i].id + '">Decline</a>';
                    rData.actions += "</div>";
                } else {
                    rData.actions += '<p class="text-gray-600 text-center m-3">No Actions</p>';
                }

                rData.actions += "</div>";

                tData.push(rData);
            }
            return tData;
        };

        ref.withdrawalsTable = $(selectors.table).DataTable({
            processing: true,
            serverSide: true,
            ajax: ({ start, length, draw }, callback) => {
                getWithdrawals(
                    {
                        pageIndex: start / length,
                        pageLength: length,
                    },
                    ({ total, data }) => {
                        ref.withdrawals = data;
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
                { title: "Status", data: "status", class: "dt-center" },
                {
                    title: "Transaction Hash",
                    data: "transaction_hash",
                    class: "dt-center",
                },
                { title: "Actions", data: "actions" },
            ],
            ordering: false,
            pageLength: 50,
            lengthMenu: [
                [50, 100, 200, 500],
                [50, 100, 200, 500],
            ],
            drawCallback: () => {
                KTMenu.createInstances();
                setEvents();
            },
        });
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

    const onApprove = function () {
        const id = $(this).data("id");
        ref.secretKeyModal.setData(null);
        ref.secretKeyModal.show((secretKey) => {
            ref.secretKeyModal.hide();
            TapSNDRUtils.alert("question", "Are you sure to <span class='fw-bold text-success'>approve</span> this withdrawal?", (eventStatus) => {
                if (!eventStatus.isConfirmed) {
                    return;
                }
                TapSNDRUtils.showLoading();
                TapSNDRUtils.ajax(
                    "post",
                    serverUrl + "/web/withdrawals",
                    {
                        id,
                        status: "APPROVED",
                        secret_key: secretKey,
                        _token: csrf_token,
                    },
                    (success, _, error) => {
                        TapSNDRUtils.hideLoading();
                        if (!success) {
                            if (error === "Internal Server Error") {
                                TapSNDRUtils.toast("error", "Please try again later.");
                            } else {
                                TapSNDRUtils.toast("error", error);
                            }
                            return;
                        }
                        TapSNDRUtils.alert("success", "Approved successfully!", () => {
                            ref.withdrawalsTable.ajax.reload();
                        });
                    }
                );
            });
        });
    };

    const onDecline = function () {
        const id = $(this).data("id");
        TapSNDRUtils.alert("question", "Are you sure to <span class='fw-bold text-danger'>decline</span> this withdrawal?", (eventStatus) => {
            if (!eventStatus.isConfirmed) {
                return;
            }
            TapSNDRUtils.showLoading();
            TapSNDRUtils.ajax(
                "post",
                serverUrl + "/web/withdrawals",
                {
                    id,
                    status: "REPORT",
                    _token: csrf_token,
                },
                (success, _, error) => {
                    TapSNDRUtils.hideLoading();
                    if (!success) {
                        TapSNDRUtils.toast("error", error);
                        return;
                    }
                    ref.withdrawalsTable.ajax.reload();
                }
            );
        });
    };

    const setEvents = () => {
        $(selectors.buttons.copyTxHash).off("click").on("click", onCopyTxHash);
        $(selectors.buttons.approve).off("click").on("click", onApprove);
        $(selectors.buttons.decline).off("click").on("click", onDecline);
    };

    return {
        init: function () {
            // Secret Key Modal
            ref.secretKeyModal = TapSNDRSecretKeyModal.getInstance(pageId + "-modal-secret_key");
            ref.secretKeyModal.init();

            setEvents();
            initDataTable();
        },
    };
})();
