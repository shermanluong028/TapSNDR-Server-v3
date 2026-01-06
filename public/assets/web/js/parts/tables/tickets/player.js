(() => {
    if (window.TapSNDRTicketsTable) return;
    window.TapSNDRTicketsTable = (() => {
        const partId = "tapsndr-table-tickets";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    table: self + " table",
                    controls: {
                        searchKey: self + " ." + partId + "-control-search_key",
                        status: self + " ." + partId + "-control-status",
                    },
                };
            })();

            const states = {
                searchParams: {},
            };
            const props = {};

            const getTableData = (data) => {
                const mapBadgeToStatus = {
                    pending: '<span class="badge badge-light-warning">Pending</span>',
                    completed: '<span class="badge badge-light-success">Completed</span>',
                    declined: '<span class="badge badge-light-danger">Declined</span>',
                    error: '<span class="badge badge-light-danger">Error</span>',
                };
                const tData = [];
                for (let i = 0; i < data.length; i++) {
                    const rData = { ...data[i] };

                    rData._id = data[i].id;

                    // Created At
                    rData.created_at = TapSNDRUtils.getDateHTML(data[i].created_at);

                    // ID
                    rData.id = TapSNDRUtils.getIDHTML(data[i].id);

                    // Client
                    rData.client = data[i].domain.client ? data[i].domain.client.username + " #" + data[i].domain.client.id : "-";

                    // Vendor Code
                    const vendorCode = data[i].domain.vendor_code;
                    rData.vendor_code =
                        "<div class='d-flex align-items-center gap-1'><span>" +
                        vendorCode +
                        "</span><a href='" +
                        serverUrl +
                        "/redeem/" +
                        vendorCode +
                        "' target='_blank'><i class='las la-external-link-alt fs-2'></i></a></div>";

                    // Amount
                    rData.amount = TapSNDRUtils.getBalanceHTML(data[i].amount);

                    // QR Code
                    // rData.qrcode =
                    //     "<a href='" + data[i].image_path + "' target='_blank'><img src='" + data[i].image_path + "' alt='' class='w-50px h-50px rounded object-fit-cover' /></a>";

                    // Status
                    rData.status = mapBadgeToStatus[data[i].status] || "-";

                    // Completion Image
                    if (data[i].status === "completed") {
                        rData.completion_image =
                            "<a href='" +
                            data[i].completion_images[0]?.image_path +
                            "' target='_blank'><img src='" +
                            data[i].completion_images[0]?.image_path +
                            "' alt='' class='w-50px h-50px rounded object-fit-cover' /></a>";
                    } else {
                        rData.completion_image = "-";
                    }

                    // Completed At
                    rData.completed_at = TapSNDRUtils.getDateHTML(data[i].completed_at);

                    tData.push(rData);
                }
                return tData;
            };

            const initDataTable = () => {
                $(selectors.table).DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: ({ start, length, draw }, callback) => {
                        props.onSearchParamsChanged(
                            {
                                ...states.searchParams,
                                pageIndex: start / length,
                                pageLength: length,
                            },
                            ({ total, data }) => {
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
                        { title: "Ticket ID", data: "ticket_id" },
                        { title: "Vendor Code", data: "vendor_code" },
                        {
                            title: "Facebook Name",
                            data: "facebook_name",
                        },
                        { title: "Game Name", data: "game" },
                        { title: "Game ID", data: "game_id" },
                        { title: "Amount", data: "amount" },
                        // {
                        //     title: "Payment Method",
                        //     data: "payment_method",
                        // },
                        // { title: "Payment Tag", data: "payment_tag" },
                        // { title: "Account Name", data: "account_name" },
                        // { title: "QR Code", data: "qrcode" },
                        { title: "Status", data: "status" },
                        { title: "Completion Image", data: "completion_image" },
                        { title: "Completed At", data: "completed_at" },
                    ],
                    ordering: false,
                    pageLength: 25,
                    lengthMenu: [
                        [25, 50, 100, 200],
                        [25, 50, 100, 200],
                    ],
                });
            };

            const reloadData = (data) => {
                $(selectors.table).DataTable().ajax.reload();
            };

            const onSearchParamsChanged = () => {
                reloadData();
            };

            const onSearchKeyChanged = function () {
                states.searchParams.searchKey = $(this).val();
                onSearchParamsChanged();
            };

            const onStatusChanged = function () {
                states.searchParams.status = $(this).val();
                onSearchParamsChanged();
            };

            const setEvents = () => {
                $(selectors.controls.searchKey).on("change", onSearchKeyChanged);
                $(selectors.controls.status).on("change", onStatusChanged);
            };

            return {
                init: ({ onSearchParamsChanged }) => {
                    props.onSearchParamsChanged = onSearchParamsChanged;
                    initDataTable();
                    setEvents();
                },
                reloadData,
            };
        };

        return {
            getInstance,
        };
    })();
})();
