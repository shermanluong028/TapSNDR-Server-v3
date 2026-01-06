window.TapSNDRPage = (() => {
    const pageId = "tapsndr-payment_details";

    const getPaymentDetailsItemHTML = (paymentDetails) =>
        '<div class="col-12 col-sm-6 h-100">' +
        '<div class="card card-dashed h-xl-100 flex-column flex-lg-row flex-stack flex-wrap p-6">' +
        '<div class="d-flex flex-column py-2">' +
        '<div class="d-flex justify-content-center justify-content-lg-start align-items-center fs-4 fw-bold mb-5">' +
        paymentDetails.method.method_name +
        "</div>" +
        '<div class="d-flex flex-column flex-lg-row align-items-center">' +
        '<img src="' +
        TapSNDRUtils.getPaymentMethodLogo(paymentDetails.method) +
        '" alt="" width="70px" class="me-lg-4" />' +
        "<div>" +
        '<div class="fs-4 fw-bold text-center text-lg-start">' +
        (paymentDetails.account_name || paymentDetails.tag || paymentDetails.email || paymentDetails.phone_number) +
        "</div>" +
        (paymentDetails.account_name
            ? '<div class="fs-6 fw-semibold text-gray-500 text-center text-lg-start">' +
              (paymentDetails.tag || paymentDetails.email || paymentDetails.phone_number) +
              "</div>"
            : "") +
        "</div>" +
        "</div>" +
        "</div>" +
        '<div class="d-flex align-items-center py-2">' +
        '<button class="btn btn-sm btn-outline btn-outline-primary me-3 ' +
        pageId +
        '-btn-edit" data-id="' +
        paymentDetails.id +
        '">Edit</button>' +
        '<button class="btn btn-sm btn-outline btn-outline-danger ' +
        pageId +
        '-btn-delete" data-id="' +
        paymentDetails.id +
        '">Delete</button>' +
        "</div>" +
        "</div>" +
        "</div>";

    const selectors = {
        list: {
            self: "." + pageId + "-list",
            buttons: {
                edit: "." + pageId + "-btn-edit",
                delete: "." + pageId + "-btn-delete",
            },
        },
        buttons: {
            addNew: "." + pageId + "-btn-add_new",
        },
    };

    const states = {
        paymentDetails: null,
    };
    const ref = {
        paymentDetailsModal: null,
    };

    const getPaymentDetails = () => {
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
                states.paymentDetails = data;
                onPaymentDetailsChanged();
            }
        );
    };

    const renderPaymentDetails = () => {
        $(selectors.list.self).empty();
        for (let i = 0; i < states.paymentDetails.length; i++) {
            const paymentDetails = states.paymentDetails[i];
            $(selectors.list.self).append(getPaymentDetailsItemHTML(paymentDetails));
        }
        if (states.paymentDetails.length === 0) {
            $(selectors.list.self).html('<p class="text-gray-600 fs-6 fw-semibold text-center">No payment methods</p>');
        }
        setEvents();
    };

    const onPaymentDetailsChanged = () => {
        renderPaymentDetails();
    };

    const onAddNew = function () {
        ref.paymentDetailsModal.setTitle("Add new payment method");
        ref.paymentDetailsModal.setData(null);
        ref.paymentDetailsModal.setVisible(1);
    };

    const onEdit = function () {
        const id = $(this).data("id");
        const paymentDetails = _.find(states.paymentDetails, { id });
        ref.paymentDetailsModal.setTitle("Edit Payment Method");
        ref.paymentDetailsModal.setData(paymentDetails);
        ref.paymentDetailsModal.setVisible(1);
    };

    const onDelete = function () {
        const id = $(this).data("id");
        TapSNDRUtils.alert("question", "Are you sure to <span class='fw-bold text-danger'>delete</span> this payment method?", (eventStatus) => {
            if (!eventStatus.isConfirmed) {
                return;
            }
            TapSNDRUtils.showLoading();
            TapSNDRUtils.ajax(
                "delete",
                serverUrl + "/web/payment_details",
                {
                    _token: csrf_token,
                    id,
                },
                (success, _, error) => {
                    TapSNDRUtils.hideLoading();
                    if (!success) {
                        return TapSNDRUtils.toast("error", error);
                    }
                    // TapSNDRUtils.alert("success", "Deleted Successfully!");
                    getPaymentDetails();
                }
            );
        });
    };

    // const onSearchParamsChanged = () => {
    //     getPaymentDetails();
    // };

    const onPaymentDetailsModalSubmit = (formData) => {
        formData.append("_token", csrf_token);
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax("post", serverUrl + "/web/payment_details", formData, true, (success, _, error) => {
            TapSNDRUtils.hideLoading();
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            getPaymentDetails();
            ref.paymentDetailsModal.setVisible(0);
        });
    };

    const setEvents = () => {
        $(selectors.buttons.addNew).off("click").on("click", onAddNew);
        $(selectors.list.buttons.edit).off("click").on("click", onEdit);
        $(selectors.list.buttons.delete).off("click").on("click", onDelete);
    };

    return {
        init: function () {
            ref.paymentDetailsModal = TapSNDRPaymentDetailsModal.getInstance(pageId + "-modal-payment_details");
            ref.paymentDetailsModal.init({
                onSubmit: onPaymentDetailsModalSubmit,
            });
            setEvents();
            getPaymentDetails();
        },
    };
})();
