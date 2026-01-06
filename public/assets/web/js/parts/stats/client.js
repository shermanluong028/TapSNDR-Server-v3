(() => {
    if (window.TapSNDRClientStats) return;
    window.TapSNDRClientStats = (() => {
        const partId = "tapsndr-stats-client";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    tickets: {
                        amount: {
                            completed: {
                                value: self + " ." + partId + "-tickets-amount-completed > span > span",
                            },
                            avg: {
                                value: self + " ." + partId + "-tickets-amount-avg > span > span",
                            },
                        },
                        count: {
                            total: {
                                value: self + " ." + partId + "-tickets-count-total > span > span",
                            },
                        },
                        fee: {
                            value: self + " ." + partId + "-tickets-fee > span > span",
                        },
                    },
                };
            })();

            const setData = (data) => {
                // Total amount completed
                $(selectors.tickets.amount.completed.value).html(Number(data.tickets.amount.completed).toLocaleString());
                // Avg amount
                $(selectors.tickets.amount.avg.value).html(Number(data.tickets.amount.avg).toLocaleString());
                // Total tickets
                $(selectors.tickets.count.total.value).html(Number(data.tickets.count.total).toLocaleString());
                // Fee
                $(selectors.tickets.fee.value).html(Number(data.tickets.fee).toLocaleString());
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
