(() => {
    if (window.TapSNDRTicketsTable) return;
    window.TapSNDRTicketsTable = (() => {
        const partId = "tapsndr-table-tickets";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    table: {
                        self: self + " table",
                        buttons: {
                            copyTicketId: "." + partId + "-btn-copy_ticket_id",
                            paymentDetails: "." + partId + "-btn-payment_details",
                            reassign: self + " ." + partId + "-btn-reassign",
                            refund: self + " ." + partId + "-btn-refund",
                        },
                    },
                    controls: {
                        searchKey: self + " ." + partId + "-control-search_key",
                        prcessingTicketsFirst: self + " ." + partId + "-control-processing_tickets_first",
                        status: self + " ." + partId + "-control-status",
                        daterange: self + " ." + partId + "-control-daterange",
                    },
                };
            })();

            const states = {
                searchParams: {
                    start_date: moment().subtract(1, "month").startOf("day").format(),
                    end_date: moment().endOf("day").format(),
                },
            };
            const props = {};
            const ref = {
                data: null,
                paymentDetailsModal: null,
                fulfillerFormModal: null,
            };

            const initDataTable = () => {
                const getTableData = (data) => {
                    const mapBadgeToStatus = {
                        pending: '<span class="badge badge-light-warning">Pending</span>',
                        validated: '<span class="badge badge-light-warning">Validated</span>',
                        processing: '<span class="badge badge-light-warning">Processing</span>',
                        completed: '<span class="badge badge-light-success">Completed</span>',
                        reported: '<span class="badge badge-light-danger">Reported</span>',
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
                        // Ticket ID
                        rData.ticket_id =
                            "<div class='d-flex justify-content-center align-items-center gap-1'><span>" +
                            data[i].ticket_id +
                            "</span><a href='javascript:void(0);' class='" +
                            partId +
                            "-btn-copy_ticket_id' data-ticket_id='" +
                            data[i].ticket_id +
                            "'><i class='las la-copy fs-2'></i></a></div>";
                        // Vendor Code
                        const capturedVendorCode = data[i].domain.domain?.match(/^([^.]+)\.tapsndr\.com$/i)?.[1];
                        rData.vendor_code = rData.vendor_code =
                            (data[i].domain.vendor_code || "-") +
                            (!data[i].domain.vendor_code || (capturedVendorCode && data[i].domain.vendor_code !== capturedVendorCode)
                                ? " (" + (capturedVendorCode || "-") + ")"
                                : "");
                        // Amount
                        rData.amount = TapSNDRUtils.getBalanceHTML(data[i].amount);
                        // Payment Methods
                        rData.payment_methods = '<div class="d-flex gap-1">';
                        const paymentDetails = data[i].player?.payment_details || [];
                        if (data[i].payment_method && data[i].payment_tag && data[i].account_name && data[i].image_path) {
                            const paymentDetailsItem = {
                                method: {
                                    method_name: data[i].payment_method,
                                },
                                account_name: data[i].account_name,
                                qrcode_url: data[i].image_path,
                            };
                            if (
                                data[i].payment_method.includes("Zelle") ||
                                data[i].payment_method.includes("PayPal") ||
                                data[i].payment_method.replace(/\s+/g, "").includes("ApplePay") ||
                                data[i].payment_method.includes("Skrill")
                            ) {
                                paymentDetailsItem.phone_number = data[i].payment_tag;
                            } else {
                                paymentDetailsItem.tag = data[i].payment_tag;
                            }
                            paymentDetails.push(paymentDetailsItem);
                        }
                        for (let j = 0; j < paymentDetails.length; j++) {
                            rData.payment_methods +=
                                '<a href="javascript:void(0);" class="' +
                                partId +
                                "-btn-payment_details\" data-payment_details='" +
                                _.escape(JSON.stringify(paymentDetails[j])) +
                                "'>" +
                                '<img class="w-30px" src="' +
                                TapSNDRUtils.getPaymentMethodLogo(paymentDetails[j].method) +
                                '" alt="' +
                                paymentDetails[j].method.method_name +
                                '" />' +
                                "</a>";
                        }
                        rData.payment_methods += "</div>";
                        // Validation Image
                        if (
                            data[i].status === "validated" ||
                            data[i].status === "processing" ||
                            data[i].status === "completed" ||
                            data[i].status === "reported"
                        ) {
                            rData.validation_image =
                                "<a href='" +
                                (serverUrl + "/web/tickets/" + data[i].id + "/validation_image") +
                                "' target='_blank'><img src='" +
                                (serverUrl + "/web/tickets/" + data[i].id + "/validation_image") +
                                "' alt='' class='w-50px h-50px rounded object-fit-cover' /></a>";
                        } else {
                            rData.validation_image = "-";
                        }
                        // Status
                        rData.status = mapBadgeToStatus[data[i].status] || "-";
                        // Completion Image
                        if (data[i].status === "completed" && data[i].completion_images.length > 0) {
                            rData.completion_image = '<div class="d-flex gap-1">';
                            for (let j = 0; j < data[i].completion_images.length; j++) {
                                rData.completion_image +=
                                    "<a href='" +
                                    data[i].completion_images[j].image_path +
                                    "' target='_blank'><img src='" +
                                    data[i].completion_images[j].image_path +
                                    "' alt='' class='w-50px h-50px rounded object-fit-cover' /></a>";
                            }
                            rData.completion_image += "</div>";
                        } else {
                            rData.completion_image = "-";
                        }
                        // Completed At
                        rData.completed_at = TapSNDRUtils.getDateHTML(data[i].completed_at);
                        tData.push(rData);
                    }
                    return tData;
                };
                $(selectors.table.self).DataTable({
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
                                ref.data = data;
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
                        { title: "Customer's Facebook Name", data: "facebook_name", class: "text-nowrap" },
                        { title: "Game Name", data: "game" },
                        { title: "Game ID", data: "game_id" },
                        { title: "Amount", data: "amount" },
                        { title: "Payment Methods", data: "payment_methods" },
                        { title: "Validation Image", data: "validation_image" },
                        { title: "Status", data: "status" },
                        { title: "Completion Image", data: "completion_image" },
                        { title: "Completed At", data: "completed_at" },
                    ],
                    fixedColumns: {
                        left: 0,
                        right: 1,
                    },
                    ordering: false,
                    pageLength: 25,
                    lengthMenu: [
                        [25, 50, 100, 200],
                        [25, 50, 100, 200],
                    ],
                    drawCallback: () => {
                        KTMenu.createInstances();
                        setEvents();
                    },
                });
            };

            const initDateRangePicker = () => {
                $(selectors.controls.daterange).daterangepicker(
                    {
                        timePicker: true,
                        timePicker24Hour: true,
                        startDate: moment().subtract(1, "month").startOf("day"),
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
                $(selectors.controls.daterange).on("cancel.daterangepicker", function () {
                    $(this).val("");
                    states.searchParams.start_date = null;
                    states.searchParams.end_date = null;
                    onSearchParamsChanged();
                });
            };

            const reloadData = () => {
                $(selectors.table.self).DataTable().ajax.reload();
            };

            const onSearchParamsChanged = () => {
                reloadData();
            };

            const onSearchKeyChanged = function () {
                states.searchParams.searchKey = $(this).val();
                onSearchParamsChanged();
            };

            const onStatusChanged = function () {
                $(this).next(".select2-container").find(".select2-selection__rendered").text($(this).find("option:selected").text());
                states.searchParams.status = $(this).val();
                onSearchParamsChanged();
            };

            const onCopyTicketId = function () {
                const ticketId = $(this).data("ticket_id");
                navigator.clipboard.writeText(ticketId).then(() => {
                    $(this).html('<i class="las la-check fs-2"></i>');
                    setTimeout(() => {
                        $(this).html('<i class="las la-copy fs-2"></i>');
                    }, 3000);
                });
            };

            const onPaymentDetails = function () {
                const paymentDetails = $(this).data("payment_details");
                ref.paymentDetailsModal.setTitle(
                    '<img class="w-40px me-3" src="' +
                        TapSNDRUtils.getPaymentMethodLogo(paymentDetails.method) +
                        '" alt="" />' +
                        paymentDetails.method.method_name
                );
                ref.paymentDetailsModal.setData(paymentDetails);
                ref.paymentDetailsModal.setMode("view");
                ref.paymentDetailsModal.setVisible(1);
            };

            const setEvents = () => {
                $(selectors.controls.searchKey).off("change").on("change", onSearchKeyChanged);
                $(selectors.controls.status).off("change").on("change", onStatusChanged);
                $(selectors.table.buttons.copyTicketId).off("click").on("click", onCopyTicketId);
                $(selectors.table.buttons.paymentDetails).off("click").on("click", onPaymentDetails);
            };

            return {
                init: ({ onSearchParamsChanged }) => {
                    props.onSearchParamsChanged = onSearchParamsChanged;

                    // Payment Details Modal
                    ref.paymentDetailsModal = TapSNDRPaymentDetailsModal.getInstance(partId + "-" + assignedId + "-modal-payment_details");
                    ref.paymentDetailsModal.init({
                        // onSubmit: onPaymentDetailsModalSubmit,
                    });

                    initDataTable();
                    initDateRangePicker();
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
