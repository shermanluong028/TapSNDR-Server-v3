window.TapSNDRMenu = (function () {
    const partId = "tapsndr-menu";

    const selectors = {
        balance: {
            value: "." + partId + "-balance > div > div > div",
        },
        items: {
            accounts: "." + partId + "-item-accounts",
            domains: "." + partId + "-item-domains",
            withdrawals: "." + partId + "-item-withdrawals",
        },
    };

    const states = {
        wallet: null,
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

    const onWalletChanged = () => {
        $(selectors.balance.value).html(states.wallet?.balance ? "$" + Number(states.wallet.balance).toLocaleString() : "-");
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
        },
        setActive,
    };
})();
