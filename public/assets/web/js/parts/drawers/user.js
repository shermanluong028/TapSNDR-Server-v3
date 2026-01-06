(() => {
    if (window.TapSNDRUserDrawer) return;
    window.TapSNDRUserDrawer = (() => {
        const partId = "tapsndr-drawer-user";

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
            let userForm = null;

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
                userForm.setData(data);
            };

            const onSubmit = function () {
                userForm.submit();
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
                    userForm = TapSNDRUserForm.getInstance(
                        partId + "-" + assignedId + "-form"
                    );
                    userForm.init({
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
