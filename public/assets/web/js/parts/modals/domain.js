(() => {
    if (window.TapSNDRDomainModal) return;
    window.TapSNDRDomainModal = (() => {
        const partId = "tapsndr-modal-domain";

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

            let domainForm = null;

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
                domainForm.setData(data);
            };

            const onSubmit = function () {
                domainForm.submit();
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

                    domainForm = TapSNDRDomainForm.getInstance(partId + "-" + assignedId + "-form");
                    domainForm.init({ onSubmit: onFormSubmit });

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
