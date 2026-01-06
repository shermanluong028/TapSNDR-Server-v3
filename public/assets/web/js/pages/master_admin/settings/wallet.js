window.TapSNDRPage = (() => {
    const pageId = "tapsndr-settings-wallet";

    const selectors = {
        wallet: {
            form: (() => {
                const self = "." + pageId + "-wallet > form";
                return {
                    self,
                    controls: {
                        address: self + " input[name='address']",
                    },
                };
            })(),
            buttons: {
                showPrivateKey: "." + pageId + "-wallet-btn-show_private_key",
            },
        },
    };

    let secretKeyModal = null;
    let privateKeyModal = null;

    const states = {
        wallet: null,
    };

    const setFormValidation = () => {
        $(selectors.wallet.form.self).validate({
            errorClass: "text-danger",
            rules: {
                address: {
                    required: true,
                },
            },
            submitHandler: function (el, e) {
                e.preventDefault();
                const data = TapSNDRUtils.getFormData(el);
                updateWallet(data, () => {
                    TapSNDRUtils.alert("success", "Saved successfully!");
                });
            },
        });
    };

    const getWallet = () => {
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax("get", serverUrl + "/web/users/" + TapSNDRCurrentUser.id + "/wallet", (success, data, error) => {
            TapSNDRUtils.hideLoading();
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            states.wallet = data;
            onWalletChanged();
        });
    };

    const updateWallet = (data, cb) => {
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "post",
            serverUrl + "/web/wallets",
            {
                id: states.wallet.id,
                ...data,
                _token: csrf_token,
            },
            (success, _, error) => {
                TapSNDRUtils.hideLoading();
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                cb();
            }
        );
    };

    const onWalletChanged = () => {
        $(selectors.wallet.form.controls.address).val(states.wallet.address);
    };

    const onShowPrivateKey = function () {
        secretKeyModal.setData(null);
        secretKeyModal.show((secretKey) => {
            const plaintext = TapSNDRUtils.decrypt(states.wallet.private_key, secretKey);
            if (!plaintext) {
                return TapSNDRUtils.toast("error", "The secret key you entered is incorrect.");
            }
            secretKeyModal.hide();
            privateKeyModal.setData({
                private_key: plaintext,
                secret_key: secretKey,
            });
            privateKeyModal.show();
        });
    };

    const onPrivateKeyModalSubmit = (data) => {
        updateWallet(data, () => {
            privateKeyModal.hide();
            TapSNDRUtils.alert("success", "Saved successfully!", () => {
                getWallet();
            });
        });
    };

    const setEvents = () => {
        $(selectors.wallet.buttons.showPrivateKey).on("click", onShowPrivateKey);
    };

    return {
        init: function () {
            // Secret Key Modal
            secretKeyModal = TapSNDRSecretKeyModal.getInstance(pageId + "-modal-secret_key");
            secretKeyModal.init();
            // Private Key Modal
            privateKeyModal = TapSNDRPrivateKeyModal.getInstance(pageId + "-modal-private_key");
            privateKeyModal.init({
                onSubmit: onPrivateKeyModalSubmit,
            });

            setFormValidation();
            setEvents();
            getWallet();
        },
    };
})();
