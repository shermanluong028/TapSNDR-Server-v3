window.TapSNDRPage = (() => {
    const pageId = "tapsndr-vendors";

    const getDomainItemHTML = (domain) =>
        '<div class="col-xl-6">' +
        '<div class="card card-dashed h-xl-100 flex-row flex-stack flex-wrap p-6">' +
        '<div class="d-flex flex-column py-2">' +
        '<div class="d-flex align-items-center">' +
        '<img src="' +
        domain.image_url +
        '" onerror="this.onerror = null; this.src=\'' +
        serverUrl +
        '/assets/web/media/default.png\'" alt="" width="70px" height="70px" class="rounded me-4 object-fit-cover" />' +
        "<div>" +
        '<div class="fs-4 fw-bold">' +
        domain.group_name +
        "</div>" +
        '<div class="fs-6 fw-semibold text-gray-500">' +
        domain.vendor_code +
        "</div>" +
        "</div>" +
        "</div>" +
        "</div>" +
        '<div class="d-flex align-items-center py-2">' +
        '<button class="btn btn-sm btn-outline btn-outline-success btn-active-light-primary me-3 ' +
        pageId +
        '-btn-submit_ticket" data-id="' +
        domain.id +
        '">Submit Ticket</button>' +
        '<button class="btn btn-sm btn-outline btn-outline-danger btn-active-light-primary ' +
        pageId +
        '-btn-delete" data-id="' +
        domain.id +
        '">Delete</button>' +
        "</div>" +
        "</div>" +
        "</div>";

    const selectors = {
        list: {
            self: "." + pageId + "-list",
            buttons: {
                submitTicket: "." + pageId + "-btn-submit_ticket",
                delete: "." + pageId + "-btn-delete",
            },
        },
        buttons: {
            addNew: "." + pageId + "-btn-add_new",
        },
    };

    let vendorCodeModal = null;
    let ticketModal = null;

    const states = {
        domains: null,
    };

    const getDomains = () => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/users/" + TapSNDRCurrentUser.id + "/domains",
            {
                with: ["commission_percentage"],
            },
            (success, data, error) => {
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                states.domains = data;
                onDomainsChanged();
            }
        );
    };

    const renderDomains = () => {
        $(selectors.list.self).empty();
        for (let i = 0; i < states.domains.length; i++) {
            const domain = states.domains[i];
            $(selectors.list.self).append(getDomainItemHTML(domain));
        }
        if (states.domains.length === 0) {
            $(selectors.list.self).html('<p class="text-gray-600 fs-6 fw-semibold text-center">No vendors</p>');
        }
        setEvents();
    };

    const onDomainsChanged = () => {
        renderDomains();
    };

    const onAddNew = function () {
        vendorCodeModal.setTitle("Add new vendor");
        vendorCodeModal.setData(null);
        vendorCodeModal.show();
    };

    const onSubmitTicket = function () {
        const id = $(this).data("id");
        const domain = _.find(states.domains, { id });
        const vendorCode = domain.vendor_code;
        ticketModal.setTitle('Submit Ticket to "' + vendorCode + '" Vendor');
        ticketModal.setData({
            player_id: TapSNDRCurrentUser.id,
            domain_id: id,
        });
        ticketModal.setDomain(domain);
        ticketModal.show();
    };

    const onDelete = function () {
        const id = $(this).data("id");
        TapSNDRUtils.alert("question", "Are you sure to <span class='fw-bold text-danger'>delete</span> this vendor?", (eventStatus) => {
            if (!eventStatus.isConfirmed) {
                return;
            }
            TapSNDRUtils.showLoading();
            TapSNDRUtils.ajax(
                "delete",
                serverUrl + "/web/users/" + TapSNDRCurrentUser.id + "/domains",
                {
                    _token: csrf_token,
                    domain_id: id,
                },
                (success, _, error) => {
                    TapSNDRUtils.hideLoading();
                    if (!success) {
                        return TapSNDRUtils.toast("error", error);
                    }
                    // TapSNDRUtils.alert("success", "Deleted Successfully!");
                    getDomains();
                }
            );
        });
    };

    const onVendorCodeModalSubmit = (data) => {
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "post",
            serverUrl + "/web/users/" + TapSNDRCurrentUser.id + "/domains",
            {
                _token: csrf_token,
                ...data,
            },
            (success, _, error) => {
                TapSNDRUtils.hideLoading();
                if (!success) {
                    if (error === "Blocked Vendor") {
                        TapSNDRUtils.toast("error", "This vendor has been blocked.");
                    } else {
                        TapSNDRUtils.toast("error", error);
                    }
                    return;
                }
                getDomains();
                vendorCodeModal.hide();
            }
        );
    };

    const onTicketModalSubmit = (data) => {
        const domain = _.find(states.domains, { id: data.domain_id });
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "post",
            serverUrl + "/web/tickets",
            {
                _token: csrf_token,
                ...data,
            },
            (success, _, error) => {
                TapSNDRUtils.hideLoading();
                if (!success) {
                    if (error === "No payment methods") {
                        TapSNDRUtils.alert(
                            "error",
                            'In order to submit a redeem ticket, please add all your payment options available. Click <a href="' +
                                serverUrl +
                                "/player/payment_details" +
                                '">here</a> to add your payment options.',
                            {
                                cancelButtonText: "Close",
                                showConfirmButton: false,
                                showCancelButton: true,
                            }
                        );
                    } else {
                        TapSNDRUtils.toast("error", error);
                    }
                    return;
                }
                ticketModal.hide();
                TapSNDRUtils.alert(
                    "success",
                    '<h4 class="text-gray-900 fw-bold">Submitted successfully!</h4>' +
                        "<div>" +
                        '<label class="fw-semibold text-muted me-2">Amount to receive:</label>' +
                        '<span class="fw-bold fs-6 text-gray-800">$' +
                        TapSNDRUtils.PN2((data.amount * (100 - domain.commission_percentage)) / 100) +
                        "</span>" +
                        "</div>",
                    (eventStatus) => {
                        if (eventStatus.isConfirmed) {
                            window.location.href = serverUrl + "/tickets";
                        }
                    },
                    {
                        confirmButtonText: "View History",
                        cancelButtonText: "Close",
                        showCancelButton: true,
                    }
                );
            }
        );
    };

    const setEvents = () => {
        $(selectors.buttons.addNew).off("click").on("click", onAddNew);
        $(selectors.list.buttons.submitTicket).off("click").on("click", onSubmitTicket);
        $(selectors.list.buttons.delete).off("click").on("click", onDelete);
    };

    return {
        init: function () {
            // Vendor Code Modal
            vendorCodeModal = TapSNDRVendorCodeModal.getInstance(pageId + "-modal-vendor_code");
            vendorCodeModal.init({
                onSubmit: onVendorCodeModalSubmit,
            });

            // Ticket Modal
            ticketModal = TapSNDRTicketModal.getInstance(pageId + "-modal-ticket");
            ticketModal.init({
                onSubmit: onTicketModalSubmit,
            });

            setEvents();
            getDomains();
        },
    };
})();
