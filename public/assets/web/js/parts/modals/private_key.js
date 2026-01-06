(() => {
    if (window.TapSNDRPrivateKeyModal) return;
    window.TapSNDRPrivateKeyModal = (() => {
        const partId = "tapsndr-modal-private_key";

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
                                private_key:
                                    self + " input[name='private_key']",
                                secret_key: self + " input[name='secret_key']",
                                secret_key1:
                                    self + " input[name='secret_key1']",
                            },
                        };
                    })(),
                    buttons: {
                        submit: self + " ." + partId + "-btn-submit",
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
                        private_key: {
                            required: true,
                        },
                        secret_key1: {
                            equalTo: selectors.form.controls.secret_key,
                        },
                    },
                    submitHandler: function (el, e) {
                        e.preventDefault();
                        const data = TapSNDRUtils.getFormData(el);
                        const ciphertext = TapSNDRUtils.encrypt(
                            data.private_key,
                            data.secret_key || states.data.secret_key
                        );
                        props.onSubmit({
                            private_key: ciphertext.toString(),
                        });
                    },
                });
            };

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
                states.data = data;
                onDataChanged();
            };

            const onDataChanged = () => {
                $(selectors.form.controls.private_key).val(
                    states.data?.private_key || ""
                );
                $(selectors.form.controls.secret_key).val("");
                $(selectors.form.controls.secret_key1).val("");
            };

            const onSubmit = function () {
                $(selectors.form.self).submit();
            };

            const setEvents = () => {
                $(selectors.buttons.submit).on("click", onSubmit);
            };

            return {
                init: ({ onSubmit }) => {
                    props.onSubmit = onSubmit;
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
