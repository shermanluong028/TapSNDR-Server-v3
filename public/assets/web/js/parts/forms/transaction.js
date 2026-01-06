(() => {
    if (window.TapSNDRTransactionForm) return;
    window.TapSNDRTransactionForm = (() => {
        const partId = "tapsndr-form-transaction";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    controls: {
                        type: self + " select[name='transaction_type']",
                        amount: self + " input[name='amount']",
                        transaction_hash: {
                            self: self + " input[name='transaction_hash']",
                            group: self + " div.form-group:has( > input[name='transaction_hash'])",
                            label: self + " div.form-group:has( > input[name='transaction_hash']) > label:first-child",
                        },
                        description: self + " textarea[name='description']",
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
                        transaction_type: {
                            required: true,
                        },
                        amount: {
                            required: true,
                            number: true,
                            min: 0,
                        },
                        transaction_hash: {
                            required: true,
                        },
                        description: {
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
                $(selectors.controls.type).val(states.data?.transaction_type || "");
                $(selectors.controls.amount).val(states.data?.amount || "");
                $(selectors.controls.transaction_hash.self).val(states.data?.transaction_hash || "");
                $(selectors.controls.description).val(states.data?.description || "");

                $(selectors.controls.transaction_hash.label).html("Transaction Hash");
                $(selectors.controls.transaction_hash.label).removeClass("required");
                $(selectors.controls.transaction_hash.self).rules("remove", "required");

                if (states.data?.transaction_type === "credit" || states.data?.transaction_type === "debit" || states.data?.transaction_type === "deposit") {
                    $(selectors.controls.transaction_hash.group).show();
                    if (states.data?.transaction_type === "credit" || states.data?.transaction_type === "debit") {
                        $(selectors.controls.transaction_hash.label).html("Ticket ID");
                        $(selectors.controls.transaction_hash.label).addClass("required");
                        $(selectors.controls.transaction_hash.self).rules("add", {
                            required: true,
                        });
                    }
                } else {
                    $(selectors.controls.transaction_hash.group).hide();
                }
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
