(() => {
    if (window.TapSNDRPaymentDetailsModal) return;
    window.TapSNDRPaymentDetailsModal = (() => {
        const partId = "tapsndr-modal-payment_details";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    title: self + " .modal-title",
                    buttons: {
                        submit: self + " ." + partId + "-btn-submit",
                        close: self + " ." + partId + "-btn-close",
                    },
                };
            })();

            let paymentDetailsForm = null;

            const props = {};

            const setVisible = (visible) => {
                $(selectors.self).modal(visible ? "show" : "hide");
            };

            const setTitle = (title) => {
                $(selectors.title).html(title);
            };

            const setData = (data) => {
                paymentDetailsForm.setData(data);
            };

            const setMode = (mode) => {
                if (mode === "view") {
                    $(selectors.buttons.submit).hide();
                    $(selectors.buttons.close).show();
                }
                paymentDetailsForm.setMode(mode);
            };

            const onSubmit = function () {
                paymentDetailsForm.submit();
            };

            const setEvents = () => {
                $(selectors.buttons.submit).on("click", onSubmit);
            };

            const onFormSubmit = (data) => {
                props.onSubmit(data);
            };

            return {
                init: ({ onSubmit }) => {
                    props.onSubmit = onSubmit;

                    paymentDetailsForm = TapSNDRPaymentDetailsForm.getInstance(partId + "-" + assignedId + "-form");
                    paymentDetailsForm.init({ onSubmit: onFormSubmit });

                    setEvents();
                },
                setVisible,
                setTitle,
                setData,
                setMode,
            };
        };

        return {
            getInstance,
        };
    })();
})();
