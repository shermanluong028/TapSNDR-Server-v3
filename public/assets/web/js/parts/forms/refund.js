(() => {
    if (window.TapSNDRRefundForm) return;
    window.TapSNDRRefundForm = (() => {
        const partId = "tapsndr-form-refund";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    ticket: {
                        ticket_id: self + " ." + partId + "-ticket-ticket_id span",
                        amount: self + " ." + partId + "-ticket-amount span",
                        client: self + " ." + partId + "-ticket-client span",
                        customer: self + " ." + partId + "-ticket-customer span",
                        fulfiller: self + " ." + partId + "-ticket-fulfiller span",
                        created_at: self + " ." + partId + "-ticket-created_at span",
                        completed_at: self + " ." + partId + "-ticket-completed_at span",
                    },
                    controls: {
                        amount: {
                            self: self + " input[name='amount']",
                            group: self + " div.form-group:has( > input[name='amount'])",
                        },
                        inconvenience_fee: {
                            self: self + " input[name='inconvenience_fee']",
                            group: self + " div.form-group:has(input[name='inconvenience_fee'])",
                            type: self + " ." + partId + "-inconvenience_fee-type",
                            result: self + " input[name='inconvenience_fee_result']",
                        },
                    },
                };
            })();

            const props = {};
            const prevStates = {
                ticket: null,
            };
            const states = { ...prevStates };

            const setFormValidation = () => {
                $(selectors.self).validate({
                    errorClass: "text-danger",
                    rules: {
                        amount: {
                            required: true,
                            min: 0,
                        },
                        inconvenience_fee: {
                            required: true,
                            min: 0,
                        },
                    },
                    errorPlacement: (err, el) => {
                        if (el.attr("name") === "inconvenience_fee") {
                            err.appendTo(selectors.controls.inconvenience_fee.group);
                        } else {
                            err.insertAfter(el);
                        }
                    },
                    submitHandler: function (_, e) {
                        e.preventDefault();
                        let inconvenienceFeeResult = states.data.inconvenience_fee;
                        if (states.data.inconvenience_fee_type === "percent") {
                            inconvenienceFeeResult = (
                                (states.data.amount /
                                    (1 -
                                        ((states.ticket.domain.commission_percentage?.admin_customer || 4) +
                                            (states.ticket.domain.commission_percentage?.distributor_customer || 0)) /
                                            100)) *
                                (states.data.inconvenience_fee / 100)
                            ).toFixed(2);
                        } else {
                        }
                        props.onSubmit({
                            ...states.data,
                            inconvenience_fee_result: inconvenienceFeeResult,
                        });
                    },
                });
            };

            const onStatesChanged = () => {
                if (states.ticket !== prevStates.ticket) {
                    $(selectors.ticket.ticket_id).html(states.ticket.ticket_id);
                    $(selectors.ticket.amount).html(TapSNDRUtils.getBalanceHTML(states.ticket.amount));
                    $(selectors.ticket.client).html(
                        states.ticket.domain.client ? states.ticket.domain.client.username + " #" + states.ticket.domain.client.id : "N/A"
                    );
                    $(selectors.ticket.customer).html(states.ticket.player ? states.ticket.player.username + " #" + states.ticket.player.id : "N/A");
                    $(selectors.ticket.fulfiller).html(states.ticket.fulfiller ? states.ticket.fulfiller.username + " #" + states.ticket.fulfiller.id : "N/A");
                    $(selectors.ticket.created_at).html(TapSNDRUtils.getDateHTML(states.ticket.created_at));
                    $(selectors.ticket.completed_at).html(TapSNDRUtils.getDateHTML(states.ticket.completed_at));

                    $(selectors.controls.amount.self).rules("add", {
                        max: Number(
                            (
                                states.ticket.amount *
                                (1 -
                                    ((states.ticket.domain.commission_percentage?.admin_customer || 4) +
                                        (states.ticket.domain.commission_percentage?.distributor_customer || 0)) /
                                        100)
                            ).toFixed(2)
                        ),
                    });
                }
                if (states.data !== prevStates.data) {
                    const amount = states.data?.amount;
                    const inconvenienceFee = states.data?.inconvenience_fee;
                    const inconvenienceFeeUnit = states.data?.inconvenience_fee_type || "percent";

                    $(selectors.controls.amount.self).val(states.data?.amount || "");
                    $(selectors.controls.inconvenience_fee.self).val(inconvenienceFee || "");

                    $(selectors.controls.inconvenience_fee.type).html(inconvenienceFeeUnit === "percent" ? "%" : "$");
                    if (inconvenienceFeeUnit === "percent") {
                        $(selectors.controls.inconvenience_fee.result).show();
                        $(selectors.controls.inconvenience_fee.self).rules("add", {
                            max: 100,
                        });
                    } else {
                        $(selectors.controls.inconvenience_fee.result).hide();
                        $(selectors.controls.inconvenience_fee.self).rules("remove", "max");
                    }

                    if (inconvenienceFeeUnit === "percent" && amount && inconvenienceFee) {
                        const inconvenienceFeeResult = (
                            (amount /
                                (1 -
                                    ((states.ticket.domain.commission_percentage?.admin_customer || 4) +
                                        (states.ticket.domain.commission_percentage?.distributor_customer || 0)) /
                                        100)) *
                            (inconvenienceFee / 100)
                        ).toFixed(2);
                        $(selectors.controls.inconvenience_fee.result).val("$ " + inconvenienceFeeResult);
                    } else {
                        $(selectors.controls.inconvenience_fee.result).val("");
                    }
                }
                prevStates.ticket = states.ticket;
                prevStates.data = states.data;
            };

            const setEvents = () => {
                for (const key in selectors.controls) {
                    $(typeof selectors.controls[key] === "object" ? selectors.controls[key].self : selectors.controls[key])
                        .off("change")
                        .on("change", function () {
                            if (!states.data) {
                                states.data = {};
                            }
                            states.data = { ...states.data };
                            states.data[$(this).attr("name")] = $(this).val();
                            onStatesChanged();
                        });
                }
                $(selectors.controls.inconvenience_fee.type)
                    .off("click")
                    .on("click", function () {
                        states.data = { ...states.data };
                        states.data.inconvenience_fee_type = states.data.inconvenience_fee_type === "percent" ? "fixed" : "percent";
                        onStatesChanged();
                    });
            };

            const setTicket = (ticket) => {
                states.ticket = ticket;
                onStatesChanged();
            };

            const setData = (data) => {
                $(selectors.self).validate().resetForm();
                states.data = data;
                onStatesChanged();
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
                setTicket,
                setData,
                submit,
            };
        };

        return {
            getInstance,
        };
    })();
})();
