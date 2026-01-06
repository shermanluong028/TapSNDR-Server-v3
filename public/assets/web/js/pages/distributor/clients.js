window.TapSNDRPage = (() => {
    const pageId = "tapsndr-clients";

    const selectors = {
        table: {
            self: "." + pageId + "-table",
            buttons: {
                edit: "." + pageId + "-btn-edit",
                editCommissionPercentage: "." + pageId + "-btn-edit-commission_percentage",
                delete: "." + pageId + "-btn-delete",
                stats: "." + pageId + "-btn-stats",
            },
        },
        buttons: {
            create: "." + pageId + "-btn-create",
        },
    };

    let userDrawer = null;
    let clientModal = null;

    let clientsTable = null;

    const states = {
        clients: null,
    };

    const getClients = () => {
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/users",
            {
                role: "user",
                orderField: "created_at",
                orderDirection: "desc",
                with: ["roles", "domains", "wallet", "commission_percentage"],
            },
            (success, data, error) => {
                TapSNDRUtils.hideLoading();
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                states.clients = data;
                onClientsChanged();
            }
        );
    };

    const drawClientsTable = () => {
        // const mapBadgeToStatus = {
        //     active: '<span class="badge badge-light-success">Active</span>',
        // };
        const tData = [];
        for (let i = 0; i < states.clients.length; i++) {
            const rData = { ...states.clients[i] };
            rData._id = states.clients[i].id;
            rData.id = TapSNDRUtils.getIDHTML(states.clients[i].id);
            rData.created_at = TapSNDRUtils.getDateHTML(states.clients[i].created_at);
            rData.username = TapSNDRUtils.getUsernameHTML(states.clients[i].username);
            if (!rData.email) {
                rData.email = TapSNDRUtils.badgeEmpty;
            }
            if (!rData.phone) {
                rData.phone = TapSNDRUtils.badgeEmpty;
            }
            rData.role = TapSNDRUtils.mapBadgeToRole[states.clients[i].roles[0]?.name] || "-";
            rData.balance = accounting.formatMoney(states.clients[i].wallet?.balance);
            // if (states.clients[i].roles[0]?.name !== "user") {
            //     rData.domains = "-";
            // } else {
            //     if (states.clients[i].form_domains.length > 0) {
            //         rData.domains = "";
            //         states.clients[i].form_domains.forEach((formDomain) => {
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

            // Total amount of completed tickets
            rData["stats_tickets_amount_completed"] = accounting.formatMoney(states.clients[i].stats.tickets?.amount?.completed);

            // Profit from this client
            // rData["stats_tickets_fee"] = accounting.formatMoney(
            //     states.clients[i].stats.tickets?.fee
            // );

            // Tickets submitted
            rData["stats_tickets_count_total"] = TapSNDRUtils.getHTML(states.clients[i].stats.tickets?.count?.total, ["fw-bold", "fs-5"]);

            // Avg ticket amount
            rData["stats_tickets_amount_avg"] = accounting.formatMoney(states.clients[i].stats.tickets?.amount?.avg);

            // Commission Percentage for TapSNDR from client
            rData["admin_client_commission_percentage"] = TapSNDRUtils.formatPercentage(states.clients[i].commission_percentage?.admin_client);

            // Commission Percentage for TapSNDR from customer
            rData["admin_customer_commission_percentage"] = TapSNDRUtils.formatPercentage(states.clients[i].commission_percentage?.admin_customer);

            // Commission Percentage for Distributor from client
            rData["distributor_client_commission_percentage"] = TapSNDRUtils.formatPercentage(states.clients[i].commission_percentage?.distributor_client);

            // Commission Percentage for Distributor from customers
            rData["distributor_customer_commission_percentage"] = TapSNDRUtils.formatPercentage(states.clients[i].commission_percentage?.distributor_customer);

            // Status
            // rData.status = mapBadgeToStatus[states.clients[i].status] || "-";

            rData.actions =
                '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions <i class="ki-duotone ki-down fs-5 ms-1"></i></a>';
            rData.actions +=
                '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-3" data-kt-menu="true">';

            // Actions - Edit
            rData.actions += '<div class="menu-item px-3">';
            rData.actions += '<a href="javascript:void(0);" class="menu-link px-3 ' + pageId + '-btn-edit" data-id="' + states.clients[i].id + '">Edit</a>';
            rData.actions += "</div>";

            // Actions - Edit Commission Percentage
            rData.actions += '<div class="menu-item px-3">';
            rData.actions +=
                '<a href="javascript:void(0);" class="menu-link px-3 ' +
                pageId +
                '-btn-edit-commission_percentage" data-id="' +
                states.clients[i].id +
                '">Edit Commission Percentage</a>';
            rData.actions += "</div>";

            // Actions - Delete
            // rData.actions += '<div class="menu-item px-3">';
            // rData.actions +=
            //     '<a href="javascript:void(0);" class="menu-link px-3 ' +
            //     pageId +
            //     '-btn-delete" data-id="' +
            //     states.clients[i].id +
            //     '">Delete</a>';
            // rData.actions += "</div>";

            // Actions - Stats
            // rData.actions += '<div class="menu-item px-3">';
            // rData.actions +=
            //     '<a href="javascript:void(0);" class="menu-link px-3 ' +
            //     pageId +
            //     '-btn-stats" data-id="' +
            //     states.clients[i].id +
            //     '" data-role="' +
            //     states.clients[i].roles[0]?.name +
            //     '">Stats</a>';
            // rData.actions += "</div>";

            rData.actions += "</div>";

            tData.push(rData);
        }
        if (clientsTable) {
            clientsTable.clear();
            clientsTable.rows.add(tData);
            clientsTable.draw();
        } else {
            clientsTable = $(selectors.table.self).DataTable({
                columns: [
                    { title: "Created At", data: "created_at" },
                    { title: "ID", data: "id" },
                    { title: "Username", data: "username" },
                    { title: "Email", data: "email" },
                    { title: "Role", data: "role" },
                    { title: "Balance", data: "balance" },
                    // { title: "Domains", data: "domains" },
                    {
                        title: "Total amount of completed tickets " + TapSNDRUtils.mapBadgeToRole["user"],
                        data: "stats_tickets_amount_completed",
                    },
                    // {
                    //     title:
                    //         "Profit from this client " +
                    //         TapSNDRUtils.mapBadgeToRole["user"],
                    //     data: "stats_tickets_fee",
                    // },
                    {
                        title: "Tickets submitted " + TapSNDRUtils.mapBadgeToRole["user"],
                        data: "stats_tickets_count_total",
                    },
                    {
                        title: "Avg ticket amount " + TapSNDRUtils.mapBadgeToRole["user"],
                        data: "stats_tickets_amount_avg",
                    },
                    {
                        title: "Commisssion Percentage For TapSNDR From Client " + TapSNDRUtils.mapBadgeToRole.user,
                        data: "admin_client_commission_percentage",
                    },
                    {
                        title: "Commisssion Percentage For TapSNDR From Customer " + TapSNDRUtils.mapBadgeToRole.user,
                        data: "admin_customer_commission_percentage",
                    },
                    {
                        title: "Commisssion Percentage For Distributor From Client " + TapSNDRUtils.mapBadgeToRole.user,
                        data: "distributor_client_commission_percentage",
                    },
                    {
                        title: "Commisssion Percentage For Distributor From Client " + TapSNDRUtils.mapBadgeToRole.user,
                        data: "distributor_customer_commission_percentage",
                    },
                    // { title: "Status", data: "status" },
                    { title: "Actions", data: "actions" },
                ],
                columnDefs: [{ targets: 4, className: "dt-center" }],
                fixedHeader: {
                    header: false,
                    footer: true,
                },
                // fixedColumns: {
                //     right: 1,
                // },
                data: tData,
                ordering: false,
                pageLength: 50,
                lengthMenu: [
                    [50, 100, 200, 500],
                    [50, 100, 200, 500],
                ],
                initComplete: function () {
                    this.api().table().header().innerHTML =
                        "<tr>" +
                        '<th rowspan="3">Created At</th>' +
                        '<th rowspan="3">ID</th>' +
                        '<th rowspan="3">Username</th>' +
                        '<th rowspan="3">Email</th>' +
                        '<th rowspan="3">Role</th>' +
                        '<th rowspan="3">Balance</th>' +
                        // '<th rowspan="3">Domains</th>' +
                        '<th colspan="3" class="dt-center border-end">Stats</th>' +
                        '<th colspan="4" class="dt-center">Commission Percentage ' +
                        "</th>" +
                        '<th rowspan="3">Actions</th>' +
                        "</tr>" +
                        "<tr>" +
                        '<th colspan="3" class="dt-center border-end">Client</th>' +
                        '<th colspan="2" class="dt-center border-end">TapSNDR</th>' +
                        '<th colspan="2" class="dt-center">Distributor</th>' +
                        "</tr>" +
                        "<tr>" +
                        '<th class="border-end">Total amount of completed tickets</th>' +
                        // '<th class="border-end">Profit from this client</th>' +
                        '<th class="border-end">Tickets submitted</th>' +
                        '<th class="border-end">Avg ticket amount</th>' +
                        '<th class="border-end">From Client</th>' +
                        '<th class="border-end">From Customer</th>' +
                        '<th class="border-end">From Client</th>' +
                        "<th>From Customer</th>" +
                        "</tr>";
                },
                drawCallback: () => {
                    KTMenu.createInstances();
                    setEvents();
                },
            });
        }
    };

    const onClientsChanged = () => {
        drawClientsTable();
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
                with: ["roles", "commission_percentage"],
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

    const onEditCommissionPercentage = function () {
        const id = $(this).data("id");
        const user = _.find(states.clients, { id });
        commissionPercentageModal.setTitle("Edit Commission Percentage for " + user.username + " #" + user.id);
        commissionPercentageModal.setData(user.commission_percentage || { client_id: id });
        commissionPercentageModal.show();
    };

    // const onDelete = function () {
    //     const id = $(this).data("id");
    //     TapSNDRUtils.alert(
    //         "question",
    //         "Are you sure you want to delete this account?",
    //         () => {
    //             TapSNDRUtils.showLoading();
    //             TapSNDRUtils.ajax(
    //                 "delete",
    //                 serverUrl + "/web/users",
    //                 {
    //                     _token: csrf_token,
    //                     id,
    //                 },
    //                 (success, _, error) => {
    //                     TapSNDRUtils.hideLoading();
    //                     if (!success) {
    //                         TapSNDRUtils.toast("error", error);
    //                         return;
    //                     }
    //                     // TapSNDRUtils.alert("success", "Deleted Successfully!");
    //                     getClients();
    //                 }
    //             );
    //         }
    //     );
    // };

    // const onStatsBtnClicked = function () {
    //     const id = $(this).data("id");
    //     const role = $(this).data("role");
    //     TapSNDRUtils.showLoading();
    //     TapSNDRUtils.ajax(
    //         "get",
    //         serverUrl + "/web/users/" + id + "/stats",
    //         (success, data, error) => {
    //             TapSNDRUtils.hideLoading();
    //             if (!success) {
    //                 TapSNDRUtils.toast("error", error);
    //                 return;
    //             }
    //             if (role === "fulfiller") {
    //                 fulfillerModal.setTitle("Fulfiller #" + id);
    //                 fulfillerModal.setData({
    //                     stats: data,
    //                 });
    //                 fulfillerModal.show();
    //             } else if (role === "user") {
    //                 clientModal.setTitle("Client #" + id);
    //                 clientModal.setData({
    //                     stats: data,
    //                 });
    //                 clientModal.show();
    //             }
    //         }
    //     );
    // };

    const onUserDrawerSubmit = (data) => {
        // const { id, username, password } = data;
        data._token = csrf_token;
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax("post", serverUrl + "/web/users", data, (success, data, error) => {
            TapSNDRUtils.hideLoading();
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            // if (!id) {
            //     TapSNDRUtils.alert("success", "<b>" + username + " #" + data.id + "</b> created successfully!<br/><br/>Password: " + password, () => {
            //         getClients();
            //         userDrawer.hide();
            //     });
            // } else {
            getClients();
            userDrawer.hide();
            // }
        });
    };

    const onCommissionPercentageModalSubmit = (data) => {
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "post",
            serverUrl + "/web/users",
            {
                _token: csrf_token,
                id: data.client_id,
                commission_percentage: _.pick(data, ["admin_client", "admin_customer", "distributor_client", "distributor_customer"]),
            },
            (success, _, error) => {
                TapSNDRUtils.hideLoading();
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                commissionPercentageModal.hide();
                TapSNDRUtils.alert("success", "Submitted successfully!", () => {
                    getClients();
                });
            }
        );
    };

    const setEvents = () => {
        $(selectors.buttons.create).off("click").on("click", onCreate);
        $(selectors.table.buttons.edit).off("click").on("click", onEdit);
        $(selectors.table.buttons.editCommissionPercentage).off("click").on("click", onEditCommissionPercentage);
        // $(selectors.table.buttons.delete).off("click").on("click", onDelete);
        // $(selectors.table.buttons.stats)
        //     .off("click")
        //     .on("click", onStatsBtnClicked);
    };

    return {
        init: function () {
            // User Drawer
            userDrawer = TapSNDRUserDrawer.getInstance(pageId + "-drawer-user");
            userDrawer.init({
                onSubmit: onUserDrawerSubmit,
            });

            // Commission Percentage Modal
            commissionPercentageModal = TapSNDRCommissionPercentageModal.getInstance(pageId + "-modal-commission_percentage");
            commissionPercentageModal.init({
                onSubmit: onCommissionPercentageModalSubmit,
            });

            // Client Modal
            // clientModal = TapSNDRClientModal.getInstance(
            //     pageId + "-modal-client"
            // );
            // clientModal.init();

            setEvents();
            getClients();
        },
    };
})();
