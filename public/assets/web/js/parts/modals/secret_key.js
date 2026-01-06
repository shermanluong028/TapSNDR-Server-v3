(() => {
    if (window.TapSNDRSecretKeyModal) return;
    window.TapSNDRSecretKeyModal = (() => {
        const partId = "tapsndr-modal-secret_key";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    title: self + " .modal-title",
                    form: (() => {
                        const self = "." + partId + "." + assignedId + " form";
                        return {
                            self,
                            controls: {
                                secret_key: self + " input[name='secret_key']",
                            },
                        };
                    })(),
                    buttons: {
                        confirm: self + " ." + partId + "-btn-confirm",
                    },
                };
            })();

            const states = {
                data: null,
            };
            const props = {};

            const setFormValidation = () => {
                $(selectors.form.self).validate({
                    errorClass: "text-danger",
                    rules: {
                        secret_key: {
                            required: true,
                        },
                    },
                    submitHandler: function (el, e) {
                        e.preventDefault();
                        const data = TapSNDRUtils.getFormData(el);
                        props.onSubmit(data["secret_key"]);
                    },
                });
            };

            const show = (onSubmit) => {
                $(selectors.self).modal("show");
                props.onSubmit = onSubmit;
            };

            const hide = () => {
                $(selectors.self).modal("hide");
            };

            const setTitle = (title) => {
                $(selectors.title).html(title);
            };

            const setData = (data) => {
                states.data = data;
                onDataChanged();
            };

            const onDataChanged = () => {
                $(selectors.form.controls.secret_key).val(states.data?.secret_key || "");
            };

            const onConfirm = function () {
                $(selectors.form.self).submit();
            };

            const setEvents = () => {
                $(selectors.buttons.confirm).on("click", onConfirm);
            };

            return {
                init: () => {
                    setFormValidation();
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
