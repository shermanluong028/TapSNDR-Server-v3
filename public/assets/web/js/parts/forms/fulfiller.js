(() => {
    if (window.TapSNDRFulfillerForm) return;
    window.TapSNDRFulfillerForm = (() => {
        const partId = "tapsndr-form-fulfiller";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    controls: {
                        id: self + " input[name='id']",
                        fulfillerId: self + " select[name='user_id']",
                    },
                };
            })();

            const props = {};
            const states = {
                data: null,
                fulfillers: null,
            };

            const setFormValidation = () => {
                $(selectors.self).validate({
                    errorClass: "text-danger",
                    rules: {
                        user_id: {
                            required: true,
                        },
                    },
                    submitHandler: function (_, e) {
                        e.preventDefault();
                        props.onSubmit(states.data);
                    },
                });
            };

            const getFulfillers = (cb) => {
                TapSNDRUtils.ajax(
                    "get",
                    serverUrl + "/web/users",
                    {
                        role: "fulfiller",
                    },
                    (success, data, error) => {
                        if (!success) {
                            TapSNDRUtils.toast("error", error);
                            return;
                        }
                        cb(data);
                    }
                );
            };

            const onFulfillersChanged = () => {
                for (let i = 0; i < states.fulfillers.length; i++) {
                    const fulfiller = states.fulfillers[i];
                    $(selectors.controls.fulfillerId).append("<option value=" + fulfiller.id + ">" + fulfiller.username + " #" + fulfiller.id + "</option>");
                }
            };

            const onDataChanged = () => {
                $(selectors.controls.id).val(states.data?.id || "");
                $(selectors.controls.fulfillerId).val(states.data?.user_id || "");
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
                    getFulfillers((fulfillers) => {
                        states.fulfillers = fulfillers;
                        onFulfillersChanged();
                    });
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
