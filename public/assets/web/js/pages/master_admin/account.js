window.TapSNDRPage = (() => {
    const pageId = "tapsndr-account";

    const selectors = {
        username: "." + pageId + "-username",
        role: (() => {
            const self = "." + pageId + "-role";
            return {
                self,
                text: self + " > :last-child",
            };
        })(),
        pendingDeposits: (() => {
            const self = "." + pageId + "-pending_deposits";
            return {
                table: {
                    self: self + " > .card-body > table",
                    buttons: {
                        delete: "." + pageId + "-pending_deposits-btn-delete",
                    },
                },
            };
        })(),
    };

    const prevStates = {
        user: null,
    };
    const states = { ...prevStates };
    const ref = {
        pendingDepositsTable: null,
    };

    const getUser = (cb) => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/users/" + TapSNDRData.id,
            {
                with: ["roles"],
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

    const getPendingDeposits = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/users/" + TapSNDRData.id + "/crypto_addresses", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const initPendingDepositsTable = () => {
        const getTableData = (data) => {
            const tData = [];
            for (let i = 0; i < data.length; i++) {
                const rData = { ...data[i] };
                rData._id = data[i].id;
                rData.id = TapSNDRUtils.getIDHTML(data[i].id);
                rData.created_at = TapSNDRUtils.getDateHTML(data[i].created_at);
                rData.address = '<p class="m-0" title="' + data[i].address + '">' + TapSNDRUtils.shortenTxHash(data[i].address) + "</p>";

                rData.actions =
                    '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-overflow="true">Actions <i class="ki-duotone ki-down fs-5 ms-1"></i></a>';
                rData.actions +=
                    '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-3" data-kt-menu="true">';
                // Actions - Delete
                rData.actions += '<div class="menu-item px-3">';
                rData.actions +=
                    '<a href="javascript:void(0);" class="menu-link px-3 ' + pageId + '-pending_deposits-btn-delete" data-id="' + data[i].id + '">Delete</a>';
                rData.actions += "</div>";

                rData.actions += "</div>";

                tData.push(rData);
            }
            return tData;
        };

        ref.pendingDepositsTable = $(selectors.pendingDeposits.table.self).DataTable({
            processing: true,
            ajax: (_, callback) => {
                getPendingDeposits((data) => {
                    ref.pendingDeposits = data;
                    callback({
                        recordsTotal: data.length,
                        recordsFiltered: data.length,
                        data: getTableData(data),
                    });
                });
            },
            columns: [
                { title: "Created At", data: "created_at" },
                { title: "ID", data: "id" },
                { title: "Address", data: "address" },
                { title: "Actions", data: "actions" },
            ],
            ordering: false,
            pageLength: 10,
            lengthMenu: [
                [10, 20, 50, 100],
                [10, 20, 50, 100],
            ],
            drawCallback: () => {
                KTMenu.createInstances();
                setEvents();
            },
        });
    };

    const onDeletePendingDeposit = function () {
        const id = $(this).data("id");
        TapSNDRUtils.alert("question", "Are you sure to <span class='fw-bold text-danger'>delete</span> this pending deposit?", (eventStatus) => {
            if (!eventStatus.isConfirmed) {
                return;
            }
            TapSNDRUtils.showLoading();
            TapSNDRUtils.ajax(
                "delete",
                serverUrl + "/web/crypto_addresses",
                {
                    _token: csrf_token,
                    id,
                },
                (success, _, error) => {
                    TapSNDRUtils.hideLoading();
                    if (!success) {
                        TapSNDRUtils.toast("error", error);
                        return;
                    }
                    ref.pendingDepositsTable.ajax.reload();
                }
            );
        });
    };

    const onStatesChanged = () => {
        if (states.user !== prevStates.user) {
            $(selectors.username).text(states.user.username);
            $(selectors.role.text).text(TapSNDRUtils.mapLabelToRole[states.user.roles[0].name]);
        }
        prevStates.user = states.user;
    };

    const setEvents = () => {
        $(selectors.pendingDeposits.table.buttons.delete).off("click").on("click", onDeletePendingDeposit);
    };

    return {
        init: function () {
            // setEvents();
            initPendingDepositsTable();
            getUser((data) => {
                states.user = data;
                onStatesChanged();
            });
        },
    };
})();
