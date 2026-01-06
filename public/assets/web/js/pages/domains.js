window.TapSNDRPage = (() => {
    const pageId = "tapsndr-domains";

    const selectors = {
        table: (() => {
            const self = "." + pageId + "-table";
            return {
                self,
                buttons: {
                    create: "." + pageId + "-btn-create",
                    edit: "." + pageId + "-btn-edit",
                    editCommissionPercentage: "." + pageId + "-btn-edit-commission_percentage",
                    // delete: "." + pageId + "-btn-delete",
                },
                controls: {
                    active: self + ' input[type="checkbox"][name="active"]',
                    original_form_enabled: self + ' input[type="checkbox"][name="original_form_enabled"]',
                },
            };
        })(),
        controls: {
            searchKey: "." + pageId + "-control-search_key",
        },
    };

    const states = {
        searchParams: {},
    };
    const ref = {
        domainsTable: null,
        domains: null,
        domainDrawer: null,
        commissionPercentageModal: null,
    };

    const getDomains = (searchParams, cb) => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/domains",
            {
                ...searchParams,
                orderField: "created_at",
                orderDirection: "desc",
                with: ["client", "games", "commission_percentage"],
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
            const mapBadgeToActive = [null, "<span class='bullet bullet-dot bg-success h-10px w-10px'></span>"];
            const getGameTag = (name) => "<span class='badge badge-danger'>" + name + "</span>";
            const tData = [];
            for (let i = 0; i < data.length; i++) {
                const rData = { ...data[i] };
                rData._id = data[i].id;
                // ID
                rData.id = TapSNDRUtils.getIDHTML(data[i].id);
                // Created At
                rData.created_at = TapSNDRUtils.getDateHTML(data[i].created_at);
                // Vendor Code
                const capturedVendorCode = data[i].domain?.match(/^([^.]+)\.tapsndr\.com$/i)?.[1];
                rData.vendor_code = "<div class='d-flex align-items-center gap-1'>";
                rData.vendor_code += "<span>";
                rData.vendor_code +=
                    (data[i].vendor_code || "-") +
                    (!data[i].vendor_code || (capturedVendorCode && data[i].vendor_code !== capturedVendorCode)
                        ? " (" + (capturedVendorCode || "-") + ")"
                        : "");
                rData.vendor_code += "</span>";
                if (data[i].vendor_code) {
                    rData.vendor_code +=
                        "<a href='https://redeem.tapsndr.com/redeem/" +
                        data[i].vendor_code +
                        "' target='_blank'><i class='las la-external-link-alt fs-2'></i></a>";
                }
                if (data[i].original_form_enabled) {
                    rData.vendor_code +=
                        "<a href='https://pay.tapsndr.com/" + data[i].vendor_code + "' target='_blank'><i class='las la-external-link-alt fs-2'></i></a>";
                }
                rData.vendor_code += "</div>";
                // Client
                rData.client = data[i].client?.username ? data[i].client.username + " #" + data[i].client.id : TapSNDRUtils.badgeEmpty;
                // Description
                rData.description = data[i].group_name || TapSNDRUtils.badgeEmpty;
                // Games
                rData.games = data[i].games.length > 0 ? data[i].games.map((game) => getGameTag(game.game_name)).join(" ") : TapSNDRUtils.badgeEmpty;
                // Telegram Chat ID
                rData.telegram_chat_id = data[i].telegram_chat_id || TapSNDRUtils.badgeEmpty;

                // Commission Percentage for TapSNDR from client
                rData["admin_client_commission_percentage"] = TapSNDRUtils.formatPercentage(data[i].commission_percentage?.admin_client);

                // Commission Percentage for TapSNDR from customer
                rData["admin_customer_commission_percentage"] = TapSNDRUtils.formatPercentage(data[i].commission_percentage?.admin_customer);

                // Commission Percentage for Distributor from client
                rData["distributor_client_commission_percentage"] = TapSNDRUtils.formatPercentage(data[i].commission_percentage?.distributor_client);

                // Commission Percentage for Distributor from customers
                rData["distributor_customer_commission_percentage"] = TapSNDRUtils.formatPercentage(data[i].commission_percentage?.distributor_customer);

                // Enable/Disable
                // rData.enabled = mapBadgeToActive[data[i].active] || "-";
                rData.enabled = '<label class="form-check form-switch form-check-custom form-check-solid">';
                rData.enabled +=
                    '<input class="form-check-input bg-danger checked:bg-success" type="checkbox" name="active" value="' +
                    data[i].id +
                    '"' +
                    (data[i].active ? " checked" : "") +
                    " />";
                rData.enabled += "</label>";

                // Enable/Disable original form
                rData.original_form_enabled = '<label class="form-check form-switch form-check-custom form-check-solid">';
                rData.original_form_enabled +=
                    '<input class="form-check-input bg-danger checked:bg-success" type="checkbox" name="original_form_enabled" value="' +
                    data[i].id +
                    '"' +
                    (data[i].original_form_enabled ? " checked" : "") +
                    " />";
                rData.original_form_enabled += "</label>";

                rData.actions =
                    '<a href="#" class="btn btn-sm btn-light btn-flex btn-center btn-active-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions <i class="ki-duotone ki-down fs-5 ms-1"></i></a>';
                rData.actions +=
                    '<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-3" data-kt-menu="true">';

                // Actions - Edit
                rData.actions += '<div class="menu-item px-3">';
                rData.actions += '<a href="javascript:void(0);" class="menu-link px-3 ' + pageId + '-btn-edit" data-id="' + data[i].id + '">Edit</a>';
                rData.actions += "</div>";

                // Actions - Edit Commission Percentage
                rData.actions += '<div class="menu-item px-3">';
                rData.actions +=
                    '<a href="javascript:void(0);" class="menu-link px-3 ' +
                    pageId +
                    '-btn-edit-commission_percentage" data-id="' +
                    data[i].id +
                    '">Edit Commission Percentage</a>';
                rData.actions += "</div>";

                // Actions - Delete
                // rData.actions += '<div class="menu-item px-3">';
                // rData.actions +=
                //     '<a href="javascript:void(0);" class="menu-link px-3 ' +
                //     pageId +
                //     '-btn-delete" data-id="' +
                //     data[i].id +
                //     '">Delete</a>';
                // rData.actions += "</div>";

                rData.actions += "</div>";

                tData.push(rData);
            }
            return tData;
        };

        ref.domainsTable = $(selectors.table.self).DataTable({
            processing: true,
            serverSide: true,
            ajax: ({ start, length, draw }, callback) => {
                getDomains(
                    {
                        ...states.searchParams,
                        pageIndex: start / length,
                        pageLength: length,
                    },
                    ({ total, data }) => {
                        ref.domains = data;
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
                { title: "Vendor Code", data: "vendor_code" },
                { title: "Client", data: "client" },
                { title: "Description", data: "description" },
                { title: "Games", data: "games" },
                {
                    title: "<div class='d-flex align-items-center'>Telegram Chat ID<i class='la la-telegram fs-2 ms-1'></i></div>",
                    data: "telegram_chat_id",
                },
                {
                    title: "Commisssion Percentage For TapSNDR From Client ",
                    data: "admin_client_commission_percentage",
                },
                {
                    title: "Commisssion Percentage For TapSNDR From Customer ",
                    data: "admin_customer_commission_percentage",
                },
                {
                    title: "Commisssion Percentage For Distributor From Client ",
                    data: "distributor_client_commission_percentage",
                },
                {
                    title: "Commisssion Percentage For Distributor From Client",
                    data: "distributor_customer_commission_percentage",
                },
                { title: "Enable/Disable", data: "enabled" },
                { title: "Enable/Disable original form", data: "original_form_enabled" },
                { title: "Actions", data: "actions" },
            ],
            ordering: false,
            pageLength: 50,
            lengthMenu: [
                [50, 100, 200, 500],
                [50, 100, 200, 500],
            ],
            fnPreDrawCallback: function () {
                this.api().table().header().innerHTML =
                    "<tr>" +
                    '<th rowspan="2">Created At</th>' +
                    '<th rowspan="2">ID</th>' +
                    '<th rowspan="2">Vendor Code</th>' +
                    '<th rowspan="2">Client</th>' +
                    '<th rowspan="2">Description</th>' +
                    '<th rowspan="2">Games</th>' +
                    '<th rowspan="2">' +
                    "<div class='d-flex align-items-center text-nowrap'>Telegram Chat ID<i class='la la-telegram fs-2 ms-1'></i></div>" +
                    "</th>" +
                    '<th colspan="4" class="dt-center">Commission Percentage</th>' +
                    '<th rowspan="2">Enable/Disable</th>' +
                    '<th rowspan="2">Enable/Disable original form</th>' +
                    '<th rowspan="2">Actions</th>' +
                    "</tr>" +
                    "<tr>" +
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
    };

    const onCreate = function () {
        ref.domainDrawer.setTitle("Create a Vendor");
        ref.domainDrawer.setData(null);
        ref.domainDrawer.show();
    };

    const onEdit = function () {
        const id = $(this).data("id");
        const domain = _.find(ref.domains, { id });
        ref.domainDrawer.setTitle("Edit Domain");
        ref.domainDrawer.setData(domain);
        ref.domainDrawer.show();
    };

    const onEditCommissionPercentage = function () {
        const id = $(this).data("id");
        const domain = _.find(ref.domains, { id });
        commissionPercentageModal.setTitle("Edit Commission Percentage for " + domain.vendor_code + " #" + domain.id);
        commissionPercentageModal.setData(domain.commission_percentage || { domain_id: id });
        commissionPercentageModal.show();
    };

    // const onDelete = function () {
    //     const id = $(this).data("id");
    //     TapSNDRUtils.alert("question", "Are you sure to <span class='fw-bold text-danger'>delete</span> this domain?", (eventStatus) => {
    //         if (!eventStatus.isConfirmed) {
    //             return;
    //         }
    //         TapSNDRUtils.showLoading();
    //         TapSNDRUtils.ajax(
    //             "delete",
    //             serverUrl + "/web/domains",
    //             {
    //                 _token: csrf_token,
    //                 id,
    //             },
    //             (success, _, error) => {
    //                 TapSNDRUtils.hideLoading();
    //                 if (!success) {
    //                     return TapSNDRUtils.toast("error", error);
    //                 }
    //                 // TapSNDRUtils.alert("success", "Deleted Successfully!");
    //                 ref.domainsTable.ajax.reload();
    //             }
    //         );
    //     });
    // };

    const onActiveChanged = function () {
        const id = $(this).val();
        const active = Number($(this).prop("checked"));
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "post",
            serverUrl + "/web/domains",
            {
                id,
                active,
                _token: csrf_token,
            },
            (success, _, error) => {
                TapSNDRUtils.hideLoading();
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
            }
        );
    };

    const onOriginalFormEnabledChanged = function () {
        const id = $(this).val();
        const originalFormEnabled = Number($(this).prop("checked"));
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "post",
            serverUrl + "/web/domains",
            {
                id,
                original_form_enabled: originalFormEnabled,
                _token: csrf_token,
            },
            (success, _, error) => {
                TapSNDRUtils.hideLoading();
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                ref.domainsTable.ajax.reload();
            }
        );
    };

    const onSearchKeyChanged = function () {
        states.searchParams.search_key = $(this).val();
        onSearchParamsChanged();
    };

    const onSearchParamsChanged = () => {
        ref.domainsTable.ajax.reload();
    };

    const onDomainDrawerSubmit = (data) => {
        data._token = csrf_token;
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax("post", serverUrl + "/web/domains", data, (success, _, error) => {
            TapSNDRUtils.hideLoading();
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            ref.domainsTable.ajax.reload();
            ref.domainDrawer.hide();
        });
    };

    const onCommissionPercentageModalSubmit = (data) => {
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "post",
            serverUrl + "/web/domains",
            {
                _token: csrf_token,
                id: data.domain_id,
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
                    ref.domainsTable.ajax.reload();
                });
            }
        );
    };

    const setEvents = () => {
        $(selectors.table.buttons.create).off("click").on("click", onCreate);
        $(selectors.table.buttons.edit).off("click").on("click", onEdit);
        $(selectors.table.buttons.editCommissionPercentage).off("click").on("click", onEditCommissionPercentage);
        // $(selectors.table.buttons.delete).off("click").on("click", onDelete);
        $(selectors.table.controls.active).off("change").on("change", onActiveChanged);
        $(selectors.table.controls.original_form_enabled).off("change").on("change", onOriginalFormEnabledChanged);
        $(selectors.controls.searchKey).off("change").on("change", onSearchKeyChanged);
    };

    return {
        init: function () {
            // Domain Drawer
            ref.domainDrawer = TapSNDRDomainDrawer.getInstance(pageId + "-drawer-domain");
            ref.domainDrawer.init({
                onSubmit: onDomainDrawerSubmit,
            });

            // Commission Percentage Modal
            commissionPercentageModal = TapSNDRCommissionPercentageModal.getInstance(pageId + "-modal-commission_percentage");
            commissionPercentageModal.init({
                onSubmit: onCommissionPercentageModalSubmit,
            });

            setEvents();
            initDataTable();
        },
    };
})();
