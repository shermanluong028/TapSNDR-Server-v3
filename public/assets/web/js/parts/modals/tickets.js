(() => {
    if (window.TapSNDRTicketsModal) return;
    window.TapSNDRTicketsModal = (() => {
        const partId = "tapsndr-modal-tickets";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    title: self + " .modal-title",
                };
            })();

            let ticketsTable = null;

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

            const reloadData = (data) => {
                ticketsTable.reloadData();
            };

            const onTicketsTableSearchParamsChanged = (searchParams, cb) => {
                props.onSearchParamsChanged(searchParams, cb);
            };

            return {
                init: ({ onSearchParamsChanged }) => {
                    props.onSearchParamsChanged = onSearchParamsChanged;

                    ticketsTable = TapSNDRTicketsTable.getInstance(partId + "-" + assignedId + "-table");
                    ticketsTable.init({
                        onSearchParamsChanged: onTicketsTableSearchParamsChanged,
                    });
                },
                show,
                hide,
                setTitle,
                reloadData,
            };
        };

        return {
            getInstance,
        };
    })();
})();
