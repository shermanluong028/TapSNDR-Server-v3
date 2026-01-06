(() => {
    if (window.TapSNDRDomainDrawer) return;
    window.TapSNDRDomainDrawer = (() => {
        const partId = "tapsndr-drawer-domain";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    buttons: {
                        submit: self + " ." + partId + "-submit",
                    },
                    title: self + " .card-title",
                };
            })();

            let ktDrawer = null;
            let domainForm = null;

            const props = {};

            const show = () => {
                ktDrawer.show();
            };

            const hide = () => {
                ktDrawer.hide();
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
                    ktDrawer = KTDrawer.getInstance(
                        document.querySelector(selectors.self)
                    );
                    domainForm = TapSNDRDomainForm.getInstance(
                        partId + "-" + assignedId + "-form"
                    );
                    domainForm.init({
                        onSubmit: onFormSubmit,
                    });
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
