(() => {
    if (window.TapSNDRCommissionPercentageModal) return;
    window.TapSNDRCommissionPercentageModal = (() => {
        const partId = "tapsndr-modal-commission_percentage";

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

            let commissionPercentageForm = null;

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

            const setData = (data) => {
                commissionPercentageForm.setData(data);
            };

            const onSubmit = function () {
                commissionPercentageForm.submit();
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

                    commissionPercentageForm = TapSNDRCommissionPercentageForm.getInstance(partId + "-" + assignedId + "-form");
                    commissionPercentageForm.init({ onSubmit: onFormSubmit });

                    setEvents();
                },
                show,
                hide,
                setTitle,
                setData,
            };
        };

        return {
            getInstance,
        };
    })();
})();
