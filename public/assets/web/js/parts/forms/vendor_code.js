(() => {
    if (window.TapSNDRVendorCodeForm) return;
    window.TapSNDRVendorCodeForm = (() => {
        const partId = "tapsndr-form-vendor_code";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    controls: {
                        vendor_code: {
                            self: self + " input[name='vendor_code']",
                            group: self + " div.form-group:has( > input[name='vendor_code'])",
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
                        vendor_code: {
                            required: true,
                        },
                    },
                    submitHandler: function (_, e) {
                        e.preventDefault();
                        props.onSubmit(states.data);
                    },
                });
            };

            const onDataChanged = () => {
                $(selectors.controls.vendor_code.self).val(states.data?.vendor_code || "");
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
