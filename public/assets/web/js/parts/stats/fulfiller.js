(() => {
    if (window.TapSNDRFulfillerStats) return;
    window.TapSNDRFulfillerStats = (() => {
        const partId = "tapsndr-stats-fulfiller";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    tickets: {
                        count: {
                            completed: {
                                value: self + " ." + partId + "-tickets-count-completed > span > span",
                            },
                            reported: {
                                value: self + " ." + partId + "-tickets-count-reported > span > span",
                            },
                            avg: {
                                "1hour": {
                                    value: self + " ." + partId + "-tickets-amount-avg-1hour > span > span",
                                },
                            },
                        },
                    },
                };
            })();

            const setData = (data) => {
                console.log(data);
                $(selectors.tickets.count.avg["1hour"].value).html(Number(data.tickets.count.avg["1hour"]).toLocaleString());
                $(selectors.tickets.count.completed.value).html(Number(data.tickets.count.completed).toLocaleString());
                $(selectors.tickets.count.reported.value).html(Number(data.tickets.count.reported).toLocaleString());
            };

            return {
                init: () => {},
                setData,
            };
        };

        return {
            getInstance,
        };
    })();
})();
