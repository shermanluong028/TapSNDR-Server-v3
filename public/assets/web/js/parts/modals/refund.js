(() => {
    if (window.TapSNDRRefundModal) return;
    window.TapSNDRRefundModal = (() => {
        const partId = "tapsndr-modal-refund";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    title: self + " .modal-title",
                    buttons: {
                        submit: self + " ." + partId + "-btn-submit",
                    },
                };
            })();

            const ref = {
                refundForm: null,
            };

            const props = {};

            const show = () => {
                $(selectors.self).modal("show");
            };

            const hide = () => {
                $(selectors.self).modal("hide");
            };

            const setTitle = (title) => {
                $(selectors.title).html(title);
            };

            const setTicket = (ticket) => {
                ref.refundForm.setTicket(ticket);
            };

            const setData = (data) => {
                ref.refundForm.setData(data);
            };

            const onSubmit = function () {
                ref.refundForm.submit();
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

                    ref.refundForm = TapSNDRRefundForm.getInstance(partId + "-" + assignedId + "-form");
                    ref.refundForm.init({ onSubmit: onFormSubmit });

                    setEvents();
                },
                show,
                hide,
                setTitle,
                setTicket,
                setData,
            };
        };

        return {
            getInstance,
        };
    })();
})();
