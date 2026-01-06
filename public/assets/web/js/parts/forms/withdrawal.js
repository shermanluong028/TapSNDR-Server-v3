(() => {
    if (window.TapSNDRWithdrawalForm) return;
    window.TapSNDRWithdrawalForm = (() => {
        const partId = "tapsndr-form-withdrawal";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    controls: {
                        admin_client: {
                            self: self + " input[name='admin_client']",
                            group: self + " div.form-group:has( > input[name='admin_client'])",
                        },
                        admin_customer: {
                            self: self + " input[name='admin_customer']",
                            group: self + " div.form-group:has( > input[name='admin_customer'])",
                        },
                        distributor_client: {
                            self: self + " input[name='distributor_client']",
                            group: self + " div.form-group:has( > input[name='distributor_client'])",
                        },
                        distributor_customer: {
                            self: self + " input[name='distributor_customer']",
                            group: self + " div.form-group:has( > input[name='distributor_customer'])",
                        },
                    },
                };
            })();

            const props = {};
            const states = {
                data: null,
            };

            const setFormValidation = () => {
                $(selectors.self).validate({
                    errorClass: "text-danger",
                    rules: {
                        admin_client: {
                            required: true,
                            min: 0,
                            max: 10,
                        },
                        admin_customer: {
                            required: true,
                            min: 0,
                            max: 10,
                        },
                        distributor_client: {
                            required: true,
                            min: 0,
                            max: 10,
                        },
                        distributor_customer: {
                            required: true,
                            min: 0,
                            max: 10,
                        },
                    },
                    submitHandler: function (_, e) {
                        e.preventDefault();
                        props.onSubmit(states.data);
                    },
                });
            };

            const onDataChanged = () => {
                $(selectors.controls.admin_client.self).val(states.data?.admin_client || "");
                $(selectors.controls.admin_customer.self).val(states.data?.admin_customer || "");
                $(selectors.controls.distributor_client.self).val(states.data?.distributor_client || "");
                $(selectors.controls.distributor_customer.self).val(states.data?.distributor_customer || "");
            };

            const setEvents = () => {
                for (const key in selectors.controls) {
                    $(typeof selectors.controls[key] === "object" ? selectors.controls[key].self : selectors.controls[key]).on("change", function () {
                        if (!states.data) {
                            states.data = {};
                        }
                        states.data[$(this).attr("name")] = $(this).val();
                        onDataChanged();
                    });
                }
            };

            const setData = (data) => {
                $(selectors.self).validate().resetForm();
                states.data = data;
                onDataChanged();
            };

            const submit = () => {
                $(selectors.self).submit();
            };

            return {
                init: ({ onSubmit }) => {
                    props.onSubmit = onSubmit;
                    setFormValidation();
                    setEvents();
                },
                setData,
                submit,
            };
        };

        return {
            getInstance,
        };
    })();
})();
