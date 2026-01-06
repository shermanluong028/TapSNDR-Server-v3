(() => {
    if (window.TapSNDRWithdrawalModal) return;
    window.TapSNDRWithdrawalModal = (() => {
        const partId = "tapsndr-modal-withdrawal";

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

            let withdrawalForm = null;

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
                withdrawalForm.setData(data);
            };

            const onSubmit = function () {
                withdrawalForm.submit();
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

                    withdrawalForm = TapSNDRWithdrawalForm.getInstance(partId + "-" + assignedId + "-form");
                    withdrawalForm.init({ onSubmit: onFormSubmit });

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
