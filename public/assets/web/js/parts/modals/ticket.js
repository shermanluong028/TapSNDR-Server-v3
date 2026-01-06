(() => {
    if (window.TapSNDRTicketModal) return;
    window.TapSNDRTicketModal = (() => {
        const partId = "tapsndr-modal-ticket";

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

            let ticketForm = null;

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
                ticketForm.setData(data);
            };

            const setDomain = (domain) => {
                ticketForm.setDomain(domain);
            };

            const onSubmit = function () {
                ticketForm.submit();
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

                    ticketForm = TapSNDRTicketForm.getInstance(partId + "-" + assignedId + "-form");
                    ticketForm.init({ onSubmit: onFormSubmit });

                    setEvents();
                },
                show,
                hide,
                setTitle,
                setData,
                setDomain,
            };
        };

        return {
            getInstance,
        };
    })();
})();
