window.TapSNDRPage = (function () {
    const pageId = "tapsndr-redeem";

    const getPaymentDetailsItemHTML = (paymentDetails) =>
        '<a href="javascript:void(0);" data-id="' +
        paymentDetails.id +
        '">' +
        '<img src="' +
        TapSNDRUtils.getPaymentMethodLogo(paymentDetails.method) +
        '" alt="" class="w-50px" />' +
        "</a>";

    const selectors = {
        header: (() => {
            const self = "." + pageId + "-header";
            return {
                title: self + " .card-title",
                buttons: {
                    signOut: "." + pageId + "-signout",
                },
            };
        })(),
        body: {
            paymentMethods: (() => {
                const self = "." + pageId + "-payment_details";
                return {
                    self,
                    item: self + " a",
                    buttons: {
                        create: "." + pageId + "-btn-create_payment_details",
                    },
                };
            })(),
        },
        footer: {
            buttons: {
                submit: "." + pageId + "-btn-submit",
            },
        },
    };

    const states = {
        domain: null,
    };
    const ref = {
        ticketForm: null,
    };

    const getDomain = (cb) => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/domains/" + TapSNDRData.vendor_code,
            {
                with: ["commission_percentage"],
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

    const getPaymentDetails = (cb) => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/payment_details",
            {
                with: ["method"],
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

    const loadPaymentDetails = () => {
        getPaymentDetails((paymentDetails) => {
            states.paymentDetails = paymentDetails;
            onPaymentDetailsChanged();
        });
    };

    const renderPaymentDetails = () => {
        $(selectors.body.paymentMethods.self).children(".ssc-line, a").remove();
        for (let i = states.paymentDetails.length - 1; i >= 0; i--) {
            $(selectors.body.paymentMethods.self).prepend(getPaymentDetailsItemHTML(states.paymentDetails[i]));
        }
        setEvents();
    };

    const onDomainChanged = () => {
        $(selectors.header.title).html(states.domain.group_name);

        ref.ticketForm.setData({
            player_id: TapSNDRCurrentUser.id,
            domain_id: states.domain.id,
        });
        ref.ticketForm.setDomain(states.domain);
    };

    const onPaymentDetailsChanged = () => {
        renderPaymentDetails();
    };

    const onSignOut = function () {
        TapSNDRUtils.ajax("post", serverUrl + "/web/auth/signout", { _token: csrf_token }, (success, _, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            window.location.reload();
        });
    };

    const onCreatePaymentDetails = function () {
        ref.paymentDetailsModal.setTitle("Add new payment method");
        ref.paymentDetailsModal.setData(null);
        ref.paymentDetailsModal.setVisible(1);
    };

    const onEditPaymentDetails = function () {
        const id = $(this).data("id");
        const paymentDetails = _.find(states.paymentDetails, { id });
        ref.paymentDetailsModal.setTitle("Edit Payment Method");
        ref.paymentDetailsModal.setData(paymentDetails);
        ref.paymentDetailsModal.setVisible(1);
    };

    const onSubmit = function () {
        ref.ticketForm.submit();
    };

    const onFormSubmit = (data) => {
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
                        TapSNDRUtils.alert("error", "In order to submit a redeem ticket, please add all your payment options available", {
                            cancelButtonText: "Close",
                            showConfirmButton: false,
                            showCancelButton: true,
                        });
                    } else {
                        TapSNDRUtils.toast("error", error);
                    }
                    return;
                }
                TapSNDRUtils.alert(
                    "success",
                    '<h4 class="text-gray-900 fw-bold">Submitted successfully!</h4>' +
                        "<div>" +
                        '<label class="fw-semibold text-muted me-2">Amount to receive:</label>' +
                        '<span class="fw-bold fs-6 text-gray-800">$' +
                        TapSNDRUtils.PN2((data.amount * (100 - states.domain.commission_percentage)) / 100) +
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

    const onPaymentDetailsModalSubmit = (formData) => {
        formData.append("_token", csrf_token);
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax("post", serverUrl + "/web/payment_details", formData, true, (success, _, error) => {
            TapSNDRUtils.hideLoading();
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            loadPaymentDetails();
            ref.paymentDetailsModal.setVisible(0);
        });
    };

    const setEvents = () => {
        $(selectors.header.buttons.signOut).off("click").on("click", onSignOut);
        $(selectors.body.paymentMethods.buttons.create).off("click").on("click", onCreatePaymentDetails);
        $(selectors.body.paymentMethods.item).off("click").on("click", onEditPaymentDetails);
        $(selectors.footer.buttons.submit).off("click").on("click", onSubmit);
    };

    return {
        init: function () {
            // Ticket Form
            ref.ticketForm = TapSNDRTicketForm.getInstance(pageId + "-form");
            ref.ticketForm.init({
                onSubmit: onFormSubmit,
            });

            // Payment Details Modal
            ref.paymentDetailsModal = TapSNDRPaymentDetailsModal.getInstance(pageId + "-modal-payment_details");
            ref.paymentDetailsModal.init({
                onSubmit: onPaymentDetailsModalSubmit,
            });

            setEvents();
            getDomain((domain) => {
                states.domain = domain;
                onDomainChanged();
            });

            if (TapSNDRCurrentUser.role === "guest") {
                TapSNDRUtils.alert(
                    "info",
                    "Please log in to TapSNDR to submit a ticket.",
                    () => {
                        window.location.href = serverUrl + "/auth/signin?redirect_to=" + encodeURIComponent(window.location.href);
                    },
                    {
                        confirmButtonText: "Go to Login",
                    }
                );
            } else {
                loadPaymentDetails();
            }
        },
    };
})();
