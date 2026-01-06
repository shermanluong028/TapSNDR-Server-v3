(() => {
    if (window.TapSNDRVendorCodeModal) return;
    window.TapSNDRVendorCodeModal = (() => {
        const partId = "tapsndr-modal-vendor_code";

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

            let vendorCodeForm = null;

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
                vendorCodeForm.setData(data);
            };

            const onSubmit = function () {
                vendorCodeForm.submit();
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

                    vendorCodeForm = TapSNDRVendorCodeForm.getInstance(partId + "-" + assignedId + "-form");
                    vendorCodeForm.init({ onSubmit: onFormSubmit });

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
