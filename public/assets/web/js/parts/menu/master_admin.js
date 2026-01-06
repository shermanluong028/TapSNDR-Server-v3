window.TapSNDRMenu = (function () {
    const partId = "tapsndr-menu";

    const selectors = {
        balance: {
            value: "." + partId + "-balance > div > div > div",
        },
        items: {
            accounts: "." + partId + "-item-accounts",
            domains: "." + partId + "-item-domains",
            tickets: "." + partId + "-item-tickets",
            withdrawals: "." + partId + "-item-withdrawals",
        },
    };

    const states = {
        wallet: null,
        stats: {
            withdrawals: null,
        },
    };

    const getWallet = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/users/" + TapSNDRCurrentUser.id + "/wallet", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const getTicketsStats = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/tickets/stats", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const getWithdrawalsStats = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/withdrawals/stats", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const onWalletChanged = () => {
        $(selectors.balance.value).html(states.wallet.balance ? "$" + Number(states.wallet.balance).toLocaleString() : "-");
    };

    const onTicketsStatsChanged = () => {
        const validatedCount = states.stats.tickets?.count.validated;
        const processingCount = states.stats.tickets?.count.processing;

        if (validatedCount + processingCount > 0) {
            $(selectors.items.tickets)
                .find(".menu-badge")
                .removeClass("d-none")
                .children(".badge")
                .html(validatedCount + processingCount);
        }
    };

    const onWithdrawalsStatsChanged = () => {
        if (states.stats.withdrawals?.count.pending > 0) {
            $(selectors.items.withdrawals).find(".menu-badge").removeClass("d-none").children(".badge").html(states.stats.withdrawals.count.pending);
        }
    };

    const setActive = (menuItem) => {
        $("." + partId + "-" + menuItem).addClass("show");
    };

    return {
        init: () => {
            getWallet((wallet) => {
                states.wallet = wallet;
                onWalletChanged();
            });
            getTicketsStats((stats) => {
                states.stats.tickets = stats;
                onTicketsStatsChanged();
            });
            getWithdrawalsStats((stats) => {
                states.stats.withdrawals = stats;
                onWithdrawalsStatsChanged();
            });
        },
        setActive,
    };
})();
