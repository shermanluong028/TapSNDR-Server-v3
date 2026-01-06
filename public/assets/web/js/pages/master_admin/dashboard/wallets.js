window.TapSNDRPage = (() => {
    const pageId = "tapsndr-dashboard-wallets";

    const getWalletItemHTML = (wallet, isFirst = false) => {
        return (
            '<div class="fs-6 d-flex justify-content-between ' +
            (isFirst ? "mb-4" : "my-4") +
            '">' +
            '<div class="fw-semibold">' +
            TapSNDRUtils.shortenTxHash(wallet.address) +
            "</div>" +
            '<div class="d-flex fw-bold">' +
            accounting.formatMoney(wallet.amount) +
            "</div>" +
            "</div>"
        );
    };

    const selectors = {
        totalBalance: (() => {
            const self = "." + pageId + "-total_balance";
            return {
                self,
                value: self + " > div:first-child",
            };
        })(),
        totalWalletAmount: (() => {
            const self = "." + pageId + "-total_wallet_amount";
            return {
                self,
                value: self + " > div:first-child",
                walletList: self + " > div:last-child",
            };
        })(),
    };

    const originalStates = {
        walletsStats: null,
        cryptoWallets: null,
    };
    const states = { ...originalStates };

    const getWalletsStats = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/wallets/stats", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const getCryptoWallets = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/crypto_wallets", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const onStatesChanged = () => {
        if (states.walletsStats !== originalStates.walletsStats) {
            $(selectors.totalBalance.value).html(accounting.formatMoney(states.walletsStats.balance.total));
        }

        if (states.cryptoWallets !== originalStates.cryptoWallets) {
            const totalWalletAmount = states.cryptoWallets.reduce((acc, wallet) => acc + Number(wallet.amount), 0);
            $(selectors.totalWalletAmount.value).html(accounting.formatMoney(totalWalletAmount));
            // Render wallet list
            $(selectors.totalWalletAmount.walletList).empty();
            for (let i = 0; i < states.cryptoWallets.length; i++) {
                const wallet = states.cryptoWallets[i];
                $(selectors.totalWalletAmount.walletList).append(getWalletItemHTML(wallet, i === 0));
                if (i !== states.cryptoWallets.length - 1) {
                    $(selectors.totalWalletAmount.walletList).append('<div class="separator separator-dashed"></div>');
                }
            }
        }

        if (states.walletsStats !== originalStates.walletsStats || states.cryptoWallets !== originalStates.cryptoWallets) {
            if (states.walletsStats && states.cryptoWallets) {
                const totalWalletAmount = states.cryptoWallets.reduce((acc, wallet) => acc + Number(wallet.amount), 0);
                const diff = states.walletsStats.balance.total - totalWalletAmount;
                $(selectors.totalBalance.value).append(
                    '<span class="fs-1 ms-1 ' +
                        (diff > 0 ? "text-danger" : "text-success") +
                        '">(' +
                        (diff > 0 ? "+" : "") +
                        accounting.formatNumber(diff, 2) +
                        ")</span>"
                );
            }
        }
    };

    // const setEvents = () => {
    //     //
    // };

    return {
        init: function () {
            // setEvents();
            getWalletsStats((data) => {
                originalStates.walletsStats = states.walletsStats;
                states.walletsStats = data;
                onStatesChanged();
            });
            getCryptoWallets((data) => {
                originalStates.walletsStats = states.walletsStats;
                states.cryptoWallets = data;
                onStatesChanged();
            });
        },
    };
})();
