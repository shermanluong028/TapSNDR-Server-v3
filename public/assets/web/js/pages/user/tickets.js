window.TapSNDRPage = (() => {
    const pageId = "tapsndr-tickets";

    const selectors = {
        icons: {
            refresh: "." + pageId + "-icon-refresh",
        },
    };

    let ticketsTable = null;

    const getTickets = (searchParams, cb) => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/tickets",
            {
                ...searchParams,
                orderField: "created_at",
                orderDirection: "desc",
                with: ["domain", "player.payment_details.method", "completion_images"],
            },
            (success, data, error) => {
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                cb(data);
            }
        );
    };

    const onRefresh = () => {
        ticketsTable.reloadData();
    };

    const setEvents = () => {
        $(selectors.icons.refresh).on("click", onRefresh);
    };

    const onTicketsTableSearchParamsChanged = (searchParams, cb) => {
        getTickets(searchParams, ({ total, data }) => {
            cb({ total, data });
        });
    };

    return {
        init: function () {
            // Tickets Table
            ticketsTable = TapSNDRTicketsTable.getInstance(pageId + "-table");
            ticketsTable.init({
                onSearchParamsChanged: onTicketsTableSearchParamsChanged,
            });

            setEvents();
        },
    };
})();
