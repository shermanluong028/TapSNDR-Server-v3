window.TapSNDRPage = (() => {
    const pageId = "tapsndr-accounts";

    const selectors = {
        table: (() => {
            const self = "." + pageId + "-table";
            return {
                self,
                body: self + " tbody",
                footer: self + " tfoot",
                buttons: {
                    edit: "." + pageId + "-btn-edit",
                    createTransaction: "." + pageId + "-btn-create_transaction",
                    tickets: "." + pageId + "-btn-tickets",
                    // stats: "." + pageId + "-btn-stats",
                    // delete: "." + pageId + "-btn-delete",
                },
                menus: self + ' [data-kt-menu="true"]',
            };
        })(),
        controls: {
            searchKey: "." + pageId + "-control-search_key",
            sortBy: "." + pageId + "-control-sort_by",
        },
        buttons: {
            create: "." + pageId + "-btn-create",
        },
        stats: {
            count: {
                total: (() => {
                    const self = "." + pageId + "-stats-count-total";
                    return {
                        self,
                        value: self + " span:first-child()",
                    };
                })(),
                distributors: (() => {
                    const self = "." + pageId + "-stats-count-distributors";
                    return {
                        self,
                        value: self + " span:first-child()",
                    };
                })(),
                fulfillers: (() => {
                    const self = "." + pageId + "-stats-count-fulfillers";
                    return {
                        self,
                        value: self + " span:first-child()",
                    };
                })(),
                clients: (() => {
                    const self = "." + pageId + "-stats-count-clients";
                    return {
                        self,
                        value: self + " span:first-child()",
                    };
                })(),
            },
        },
    };

    let userDrawer = null;
    let transactionModal = null;
    let ticketsModal = null;
    // let fulfillerModal = null;
    // let clientModal = null;

    const states = {
        users: null,
        stats: null,
        searchParams: {
            orderField: "created_at",
            orderDirection: "desc",
        },
    };
    const ref = {
        usersTable: null,
        users: null,
        originalSearchParams: null,
    };

    const getUsers = (searchParams, cb) => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/users",
            {
                ...searchParams,
                with: ["roles", "wallet", "domains"],
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

    const getTickets = (searchParams, cb) => {
        if (!ref.user) {
            return;
        }
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/users/" + ref.user.id + "/tickets",
            {
                ...searchParams,
                orderField: "created_at",
                orderDirection: "desc",
                with: ["domain.client", "fulfiller", "completion_images"],
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

    const getStats = () => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/users/stats", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            states.stats = data;
            onStatsChanged();
        });
    };

    const getWalletsStats = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/wallets/stats", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const getTicketsStats = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/tickets/stats", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const initDataTable = () => {
        const getTableData = (data) => {
            // const mapBadgeToStatus = {
            //     active: '<span class="badge badge-light-success">Active</span>',
            // };
            const tData = [];
            for (let i = 0; i < data.length; i++) {
                const rData = { ...data[i] };
                rData._id = data[i].id;
                rData.id = TapSNDRUtils.getIDHTML(data[i].id);
                rData.created_at = TapSNDRUtils.getDateHTML(data[i].created_at);
                rData.username = '<a href="' + serverUrl + "/accounts/" + data[i].id + '">' + TapSNDRUtils.getUsernameHTML(data[i].username) + "</a>";
                if (!rData.email) {
                    rData.email = TapSNDRUtils.badgeEmpty;
                }
                if (!rData.phone) {
                    rData.phone = TapSNDRUtils.badgeEmpty;
                }
                rData.role = TapSNDRUtils.mapBadgeToRole[data[i].roles[0]?.name] || "-";
                rData.balance = TapSNDRUtils.getBalanceHTML(data[i].wallet?.balance);
                // if (data[i].roles[0]?.name !== "user") {
                //     rData.domains = "-";
                // } else {
                //     if (data[i].form_domains.length > 0) {
                //         rData.domains = "";
                //         data[i].form_domains.forEach((formDomain) => {
                //             rData.domains +=
                //                 "<a class='text-decoration-underline' href='https://" +
                //                 formDomain.domain +
                //                 "' target='_blank'>" +
                //                 formDomain.domain +
                //                 "</a><br>";
                //         });
                //     } else {
                //         rData.domains = TapSNDRUtils.badgeEmpty;
                //     }
                // }

                // --------------------
                // Stats
                // --------------------

                // Avg tickets processed in 1 hour
                rData["stats_tickets_count_avg_1hour"] = data[i].stats.tickets?.count?.avg?.["1hour"] || "-";

                // Tickets processe
                rData["stats_tickets_count_completed"] = data[i].stats.tickets?.count?.completed || "-";

                // Tickets reported
                rData["stats_tickets_count_reported"] = data[i].stats.tickets?.count?.reported || "-";

                // Total amount of completed tickets
                rData["stats_tickets_amount_completed"] = TapSNDRUtils.getBalanceHTML(data[i].stats.tickets?.amount?.completed);

                // Avg daily completed ticket amount
                rData["avg_daily_completed_ticket_amount"] = TapSNDRUtils.getBalanceHTML(data[i].stats.tickets?.amount?.avg?.daily_completed);

                // Profit from this client
                rData["stats_tickets_fee"] = TapSNDRUtils.getBalanceHTML(data[i].stats.tickets?.fee);

                // Tickets submitted
                rData["stats_tickets_count_total"] = data[i].stats.tickets?.count?.total || "-";

                // Avg ticket amount
                rData["stats_tickets_amount_avg"] = TapSNDRUtils.getBalanceHTML(data[i].stats.tickets?.amount?.avg?.total);

                // Last ticket At
                rData["stats_tickets_date_last"] = TapSNDRUtils.getDateHTML(data[i].stats.tickets?.date?.last);

                // Status
                // rData.status = mapBadgeToStatus[data[i].status] || "-";

                rData.actions =
                    '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-overflow="true">Actions <i class="ki-duotone ki-down fs-5 ms-1"></i></a>';
                rData.actions +=
                    '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-3" data-kt-menu="true">';

                // Actions - Edit
                rData.actions += '<div class="menu-item px-3">';
                rData.actions += '<a href="javascript:void(0);" class="menu-link px-3 ' + pageId + '-btn-edit" data-id="' + data[i].id + '">Edit</a>';
                rData.actions += "</div>";

                // Actions - Credit / Debit
                rData.actions += '<div class="menu-item px-3">';
                rData.actions +=
                    '<a href="javascript:void(0);" class="menu-link px-3 ' +
                    pageId +
                    '-btn-create_transaction" data-id="' +
                    data[i].id +
                    '">Credit / Debit</a>';
                rData.actions += "</div>";

                if (data[i].roles[0]?.name === "fulfiller") {
                    // Actions - Tickets
                    rData.actions += '<div class="menu-item px-3">';
                    rData.actions += '<a href="javascript:void(0);" class="menu-link px-3 ' + pageId + '-btn-tickets" data-id="' + data[i].id + '">Tickets</a>';
                    rData.actions += "</div>";
                }

                // Actions - Stats
                // rData.actions += '<div class="menu-item px-3">';
                // rData.actions +=
                //     '<a href="javascript:void(0);" class="menu-link px-3 ' +
                //     pageId +
                //     '-btn-stats" data-id="' +
                //     data[i].id +
                //     '" data-role="' +
                //     data[i].roles[0]?.name +
                //     '">Stats</a>';
                // rData.actions += "</div>";

                // Actions - Delete
                // rData.actions += '<div class="menu-item px-3">';
                // rData.actions += '<a href="javascript:void(0);" class="menu-link px-3 ' + pageId + '-btn-delete" data-id="' + data[i].id + '">Delete</a>';
                // rData.actions += "</div>";

                rData.actions += "</div>";

                tData.push(rData);
            }
            return tData;
        };

        if (ref.usersTable) {
            ref.usersTable.destroy();
            $(selectors.table.self).empty();
        }

        if (!states.searchParams.role) {
            let footerHTML =
                "<tfoot>" +
                '<tr class="fw-bold fs-5">' +
                '<th colspan="5" class="text-nowrap align-end !text-start">Total:</th>' +
                '<th class="text-danger">' +
                '<div class="spinner-border spinner-border-sm text-dark"></div>' +
                "</th>" +
                '<th colspan="3"></th>' +
                '<th class="text-danger">' +
                '<div class="spinner-border spinner-border-sm text-dark"></div>' +
                "</th>" +
                '<th class="text-danger">' +
                // '<div class="spinner-border spinner-border-sm text-dark"></div>' +
                "</th>" +
                '<th class="text-danger">' +
                '<div class="spinner-border spinner-border-sm text-dark"></div>' +
                "</th>" +
                '<th class="text-danger">' +
                '<div class="spinner-border spinner-border-sm text-dark"></div>' +
                "</th>" +
                "<th></th>" +
                "</tr>" +
                "</tfoot>";
            $(selectors.table.self).append(footerHTML);
        }

        ref.usersTable = $(selectors.table.self).DataTable({
            processing: true,
            serverSide: true,
            ajax: ({ start, length, draw }, callback) => {
                getUsers(
                    {
                        ...states.searchParams,
                        pageIndex: start / length,
                        pageLength: length,
                    },
                    ({ total, data }) => {
                        ref.users = data;
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
                { title: "Username", data: "username" },
                { title: "Email", data: "email" },
                { title: "Role", data: "role" },
                { title: "Balance", data: "balance" },
                // { title: "Domains", data: "domains" },
                ...(!states.searchParams.role || states.searchParams.role === "fulfiller"
                    ? [
                          {
                              title: "Avg tickets processed in 1 hour " + TapSNDRUtils.mapBadgeToRole["fulfiller"],
                              data: "stats_tickets_count_avg_1hour",
                          },
                          {
                              title: "Tickets processed " + TapSNDRUtils.mapBadgeToRole["fulfiller"],
                              data: "stats_tickets_count_completed",
                          },
                          {
                              title: "Tickets reported " + TapSNDRUtils.mapBadgeToRole["fulfiller"],
                              data: "stats_tickets_count_reported",
                          },
                      ]
                    : []),
                ...(!states.searchParams.role || states.searchParams.role === "user"
                    ? [
                          {
                              title: "Total amount of completed tickets " + TapSNDRUtils.mapBadgeToRole["user"],
                              data: "stats_tickets_amount_completed",
                          },
                          {
                              title: "Avg daily completed ticket amount " + TapSNDRUtils.mapBadgeToRole["user"],
                              data: "avg_daily_completed_ticket_amount",
                          },
                          {
                              title: "Profit from this client " + TapSNDRUtils.mapBadgeToRole["user"],
                              data: "stats_tickets_fee",
                          },
                          {
                              title: "Tickets submitted " + TapSNDRUtils.mapBadgeToRole["user"],
                              data: "stats_tickets_count_total",
                          },
                          {
                              title: "Avg ticket amount " + TapSNDRUtils.mapBadgeToRole["user"],
                              data: "stats_tickets_amount_avg",
                          },
                          {
                              title: "Last ticket at " + TapSNDRUtils.mapBadgeToRole["user"],
                              data: "stats_tickets_date_last",
                          },
                      ]
                    : []),
                // { title: "Status", data: "status" },
                { title: "Actions", data: "actions" },
            ],
            // columnDefs: [{ targets: 4, className: "dt-center" }],
            fixedHeader: {
                header: false,
                footer: true,
            },
            fixedColumns: {
                left: 0,
                right: 1,
            },
            ordering: false,
            pageLength: 50,
            lengthMenu: [
                [50, 100, 200, 500],
                [50, 100, 200, 500],
            ],
            fnPreDrawCallback: function () {
                let headerHTML =
                    "<tr>" +
                    '<th rowspan="3">Created At</th>' +
                    '<th rowspan="3">ID</th>' +
                    '<th rowspan="3">Username</th>' +
                    '<th rowspan="3">Email</th>' +
                    '<th rowspan="3">Role</th>' +
                    '<th rowspan="3">Balance</th>';
                // '<th rowspan="3">Domains</th>';

                // Stats
                if (!states.searchParams.role || states.searchParams.role === "fulfiller" || states.searchParams.role === "user") {
                    let colspan = 9;
                    if (states.searchParams.role === "fulfiller") {
                        colspan = 3;
                    } else if (states.searchParams.role === "user") {
                        colspan = 6;
                    }
                    headerHTML += '<th colspan="' + colspan + '" class="dt-center">Stats</th>';
                }

                // headerHTML += '<th class="dtfc-fixed-right position-sticky end-0" rowspan="3">Actions</th>';
                headerHTML += '<th rowspan="3" class="dtfc-fixed-right position-sticky end-0">Actions</th>';

                headerHTML += "</tr><tr>";

                // Stats - Fulfiller
                if (!states.searchParams.role || states.searchParams.role === "fulfiller") {
                    headerHTML += '<th colspan="3" class="dt-center';
                    if (!states.searchParams.role) {
                        headerHTML += " border-end";
                    }
                    headerHTML += '">Fulfiller</th>';
                }

                // Stats - Client
                if (!states.searchParams.role || states.searchParams.role === "user") {
                    headerHTML += '<th colspan="6" class="dt-center">Client</th>';
                }

                headerHTML += "</tr><tr>";

                // Stats - Fulfiller Columns
                if (!states.searchParams.role || states.searchParams.role === "fulfiller") {
                    headerHTML += "<th>Avg tickets processed in 1 hour</th>" + "<th>Tickets processed</th>" + "<th class='";
                    if (!states.searchParams.role) {
                        headerHTML += " border-end";
                    }
                    headerHTML += "'>Tickets reported</th>";
                }

                // Stats - Client Columns
                if (!states.searchParams.role || states.searchParams.role === "user") {
                    headerHTML +=
                        "<th>Total amount of completed tickets</th>" +
                        "<th>Avg daily completed ticket amount</th>" +
                        "<th>Profit from this client</th>" +
                        "<th>Tickets submitted</th>" +
                        "<th>Avg ticket amount</th>" +
                        "<th>Last ticket At</th>";
                }

                headerHTML += "</tr>";

                this.api().table().header().innerHTML = headerHTML;
            },
            rowCallback: (row, data) => {
                const user = _.find(ref.users, { id: data._id });
                if (user.stats.tickets?.date?.last) {
                    const passedDays = moment().diff(moment(user.stats.tickets.date.last), "days");
                    if (passedDays < 1) {
                        $(row).addClass("bg-light-success").children().addClass("shadow-none");
                    } else if (passedDays > 30) {
                        $(row).addClass("bg-light-warning").children().addClass("shadow-none");
                    }
                }
            },
            drawCallback: () => {
                KTMenu.createInstances();
                // const menus = $(selectors.table.menus);
                // menus.each(function () {
                //     const menu = KTMenu.getInstance(this);
                //     menu.on("kt.menu.dropdown.show", function () {
                //         $(menu.element).parent().addClass("z-5");
                //     });
                //     menu.on("kt.menu.dropdown.hide", function () {
                //         $(menu.element).parent().removeClass("z-5");
                //     });
                // });
                setEvents();
            },
        });

        if (!states.searchParams.role) {
            getWalletsStats((data) => {
                $(ref.usersTable.column(5).footer()).html(accounting.formatMoney(data.balance.total));
                // ref.usersTable.columns.adjust().draw();
            });
            getTicketsStats((data) => {
                $(ref.usersTable.column(9).footer()).html(accounting.formatMoney(data.amount.completed));
                // $(usersTable.column(10).footer()).html(
                //     accounting.formatMoney(data.fee)
                // );
                $(ref.usersTable.column(11).footer()).html(accounting.formatNumber(data.count.total));
                $(ref.usersTable.column(12).footer()).html(accounting.formatMoney(data.amount.avg));
                // ref.usersTable.columns.adjust().draw();
            });
        }
    };

    const onStatsChanged = () => {
        $(selectors.stats.count.total.value).html(Number(states.stats.count.total).toLocaleString());
        $(selectors.stats.count.distributors.value).html(Number(states.stats.count.distributors).toLocaleString());
        $(selectors.stats.count.clients.value).html(Number(states.stats.count.clients).toLocaleString());
        $(selectors.stats.count.fulfillers.value).html(Number(states.stats.count.fulfillers).toLocaleString());
    };

    const onCreate = function () {
        userDrawer.setTitle("Create Account");
        userDrawer.setData(null);
        userDrawer.show();
    };

    const onEdit = function () {
        const id = $(this).data("id");
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/users/" + id,
            {
                with: ["roles"],
            },
            (success, data, error) => {
                TapSNDRUtils.hideLoading();
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                userDrawer.setTitle("Edit Account");
                userDrawer.setData(data);
                userDrawer.show();
            }
        );
    };

    const onCreateTransaction = function () {
        const id = $(this).data("id");
        const user = _.find(ref.users, { id });
        transactionModal.setTitle("Credit / Debit for " + user.username + " #" + user.id);
        transactionModal.setData({
            user_id: id,
        });
        transactionModal.show();
    };

    // const onDelete = function () {
    //     const id = $(this).data("id");
    //     TapSNDRUtils.alert("question", "Are you sure to <span class='fw-bold text-danger'>delete</span> this account?", (eventStatus) => {
    //         if (!eventStatus.isConfirmed) {
    //             return;
    //         }
    //         TapSNDRUtils.showLoading();
    //         TapSNDRUtils.ajax(
    //             "delete",
    //             serverUrl + "/web/users",
    //             {
    //                 _token: csrf_token,
    //                 id,
    //             },
    //             (success, _, error) => {
    //                 TapSNDRUtils.hideLoading();
    //                 if (!success) {
    //                     TapSNDRUtils.toast("error", error);
    //                     return;
    //                 }
    //                 // TapSNDRUtils.alert("success", "Deleted Successfully!");
    //                 ref.usersTable.ajax.reload();
    //             }
    //         );
    //     });
    // };

    // const onStatsBtnClicked = function () {
    //     const id = $(this).data("id");
    //     const role = $(this).data("role");
    //     TapSNDRUtils.showLoading();
    //     TapSNDRUtils.ajax("get", serverUrl + "/web/users/" + id + "/stats", (success, data, error) => {
    //         TapSNDRUtils.hideLoading();
    //         if (!success) {
    //             TapSNDRUtils.toast("error", error);
    //             return;
    //         }
    //         if (role === "fulfiller") {
    //             fulfillerModal.setTitle("Fulfiller #" + id);
    //             fulfillerModal.setData({
    //                 stats: data,
    //             });
    //             fulfillerModal.show();
    //         } else if (role === "user") {
    //             clientModal.setTitle("Client #" + id);
    //             clientModal.setData({
    //                 stats: data,
    //             });
    //             clientModal.show();
    //         }
    //     });
    // };

    const onTicketsBtnClicked = function () {
        const id = $(this).data("id");
        const user = ref.users.find((u) => u.id === id);
        ref.user = user;
        ticketsModal.reloadData();
        ticketsModal.setTitle("Tickets of Fulfiller #" + id + " " + user.username);
        ticketsModal.show();
    };

    const onUserDrawerSubmit = (data) => {
        // const { id, username, password } = data;
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "post",
            serverUrl + "/web/users",
            {
                _token: csrf_token,
                ...data,
            },
            (success, data, error) => {
                TapSNDRUtils.hideLoading();
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                // if (!id) {
                //     TapSNDRUtils.alert("success", "<b>" + username + " #" + data.id + "</b> created successfully!<br/><br/>Password: " + password, () => {
                //         ref.usersTable.ajax.reload();
                //         userDrawer.hide();
                //     });
                // } else {
                ref.usersTable.ajax.reload();
                userDrawer.hide();
                // }
            }
        );
    };

    const onTransactionModalSubmit = (data) => {
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "post",
            serverUrl + "/web/transactions",
            {
                ...data,
                _token: csrf_token,
            },
            (success, _, error) => {
                TapSNDRUtils.hideLoading();
                if (!success) {
                    if (error === "transaction_hash is invalid.") {
                        error = "Invalid Ticket ID";
                    }
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                transactionModal.hide();
                TapSNDRUtils.alert("success", "Submitted successfully!", () => {
                    ref.usersTable.ajax.reload();
                });
            }
        );
    };

    const onSearchParamsChanged = () => {
        for (const key in selectors.stats.count) {
            $(selectors.stats.count[key].self).removeClass("border");
        }
        if (states.searchParams.role === "distributor") {
            $(selectors.stats.count.distributors.self).addClass("border");
        } else if (states.searchParams.role === "fulfiller") {
            $(selectors.stats.count.fulfillers.self).addClass("border");
        } else if (states.searchParams.role === "user") {
            $(selectors.stats.count.clients.self).addClass("border");
        } else {
            $(selectors.stats.count.total.self).addClass("border");
        }
        if (states.searchParams.role !== ref.originalSearchParams?.role) {
            initDataTable();
        } else {
            ref.usersTable.ajax.reload();
        }
    };

    const onSearchKeyChanged = function () {
        ref.originalSearchParams = { ...states.searchParams };
        states.searchParams.search_key = $(this).val();
        onSearchParamsChanged();
    };

    const onSortByChanged = function () {
        $(this).next(".select2-container").find(".select2-selection__rendered").text($(this).find("option:selected").text());
        const matches = $(this)
            .val()
            .match(/(.*)_(asc|desc)$/);
        ref.originalSearchParams = { ...states.searchParams };
        states.searchParams.orderField = matches[1];
        states.searchParams.orderDirection = matches[2];
        onSearchParamsChanged();
    };

    const onTotalCountClicked = function () {
        ref.originalSearchParams = { ...states.searchParams };
        delete states.searchParams.role;
        onSearchParamsChanged();
    };

    const onDistributorsCountClicked = function () {
        ref.originalSearchParams = { ...states.searchParams };
        states.searchParams.role = "distributor";
        onSearchParamsChanged();
    };

    const onFulfillersCountClicked = function () {
        ref.originalSearchParams = { ...states.searchParams };
        states.searchParams.role = "fulfiller";
        onSearchParamsChanged();
    };

    const onClientsCountClicked = function () {
        ref.originalSearchParams = { ...states.searchParams };
        states.searchParams.role = "user";
        onSearchParamsChanged();
    };

    const setEvents = () => {
        $(selectors.buttons.create).off("click").on("click", onCreate);
        $(selectors.table.buttons.edit).off("click").on("click", onEdit);
        $(selectors.table.buttons.createTransaction).off("click").on("click", onCreateTransaction);
        $(selectors.table.buttons.tickets).off("click").on("click", onTicketsBtnClicked);
        // $(selectors.table.buttons.stats).off("click").on("click", onStatsBtnClicked);
        // $(selectors.table.buttons.delete).off("click").on("click", onDelete);
        $(selectors.controls.searchKey).off("change").on("change", onSearchKeyChanged);
        $(selectors.controls.sortBy).off("change").on("change", onSortByChanged);
        $(selectors.stats.count.total.self).off("click").on("click", onTotalCountClicked);
        $(selectors.stats.count.distributors.self).off("click").on("click", onDistributorsCountClicked);
        $(selectors.stats.count.fulfillers.self).off("click").on("click", onFulfillersCountClicked);
        $(selectors.stats.count.clients.self).off("click").on("click", onClientsCountClicked);
    };

    const onTicketsModalSearchParamsChanged = (searchParams, cb) => {
        getTickets(searchParams, ({ total, data }) => {
            cb({ total, data });
        });
    };

    return {
        init: function () {
            // User Drawer
            userDrawer = TapSNDRUserDrawer.getInstance(pageId + "-drawer-user");
            userDrawer.init({
                onSubmit: onUserDrawerSubmit,
            });

            // Transaction Modal
            transactionModal = TapSNDRTransactionModal.getInstance(pageId + "-modal-transaction");
            transactionModal.init({
                onSubmit: onTransactionModalSubmit,
            });

            // Tickets Modal
            ticketsModal = TapSNDRTicketsModal.getInstance(pageId + "-modal-tickets");
            ticketsModal.init({ onSearchParamsChanged: onTicketsModalSearchParamsChanged });

            // Fulfiller Modal
            // fulfillerModal = TapSNDRFulfillerModal.getInstance(pageId + "-modal-fulfiller");
            // fulfillerModal.init();

            // Client Modal
            // clientModal = TapSNDRClientModal.getInstance(pageId + "-modal-client");
            // clientModal.init();

            setEvents();
            initDataTable();
            onSearchParamsChanged();
            getStats();
        },
    };
})();
