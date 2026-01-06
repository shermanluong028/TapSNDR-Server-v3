(() => {
    if (window.TapSNDRTicketForm) return;
    window.TapSNDRTicketForm = (() => {
        const partId = "tapsndr-form-ticket";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    commissionPercentage: self + " ." + partId + "-commission_percentage",
                    controls: {
                        facebook_name: self + " input[name='facebook_name']",
                        amount: self + " input[name='amount']",
                        game: self + " select[name='game']",
                        game_id: self + " input[name='game_id']",
                        // payment_details_id: {
                        //     self: self + " select[name='payment_details_id']",
                        //     group: self + " div.form-group:has(select[name='payment_details_id'])",
                        // },
                    },
                };
            })();

            const props = {};
            const states = {
                data: null,
                domain: null,
                games: null,
                paymentDetails: null,
            };
            const ref = {
                originalData: null,
            };

            const setFormValidation = () => {
                $(selectors.self).validate({
                    errorClass: "text-danger",
                    rules: {
                        facebook_name: {
                            required: true,
                        },
                        amount: {
                            required: true,
                            min: 100,
                        },
                        game: {
                            required: true,
                        },
                        game_id: {
                            required: true,
                        },
                        // payment_details_id: {
                        //     required: true,
                        // },
                    },
                    errorPlacement: (err, el) => {
                        // if (el.attr("name") === "payment_details_id") {
                        //     err.appendTo(selectors.controls.payment_details_id.group);
                        // } else {
                        //     err.insertAfter(el);
                        // }
                        err.insertAfter(el);
                    },
                    submitHandler: function (_, e) {
                        e.preventDefault();
                        props.onSubmit(states.data);
                    },
                });
            };

            const getGames = (cb) => {
                $(selectors.controls.game).attr("disabled", true);
                $(selectors.controls.game).html('<option value="">Loading...</option>');
                TapSNDRUtils.ajax("get", serverUrl + "/web/domains/" + states.data?.domain_id + "/games", (success, data, error) => {
                    if (!success) {
                        TapSNDRUtils.toast("error", error);
                        return;
                    }
                    cb(data);
                });
            };

            const getPaymentDetails = (cb) => {
                TapSNDRUtils.ajax(
                    "get",
                    serverUrl + "/web/payment_details",
                    {
                        with: ["method"],
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

            const onGamesChanged = () => {
                if (states.games.length === 0) {
                    $(selectors.controls.game).html('<option value="">No Games</option>');
                    return;
                }
                $(selectors.controls.game).empty();
                for (let i = 0; i < states.games.length; i++) {
                    const game = states.games[i];
                    $(selectors.controls.game).append('<option value="' + game.game_name + '">' + game.game_name + "</option>");
                }
                $(selectors.controls.game).attr("disabled", false);
            };

            // const onPaymentDetailsChanged = () => {
            //     setPaymentDetailsSelect2();
            // };

            const onDataChanged = () => {
                $(selectors.controls.facebook_name).val(states.data?.facebook_name || "");
                $(selectors.controls.amount).val(states.data?.amount || "");
                $(selectors.controls.game).val(states.data?.game || "");
                $(selectors.controls.game_id).val(states.data?.game_id || "");
                // $(selectors.controls.payment_details_id.self).val(states.data?.payment_details_id || "");

                // if (ref.originalData?.payment_details_id !== states.data?.payment_details_id) {
                //     $(selectors.controls.payment_details_id.self).trigger("change");
                // }

                if (ref.originalData?.domain_id !== states.data?.domain_id) {
                    getGames((games) => {
                        states.games = games;
                        onGamesChanged();
                    });
                }
            };

            const onDomainChanged = () => {
                $(selectors.commissionPercentage).html(states.domain.commission_percentage);
            };

            // const setPaymentDetailsSelect2 = () => {
            //     $(selectors.controls.payment_details_id.self).html("<option></option>");
            //     for (let i = 0; i < states.paymentDetails.length; i++) {
            //         const paymentDetails = states.paymentDetails[i];
            //         $(selectors.controls.payment_details_id.self).append(
            //             '<option value="' +
            //                 paymentDetails.id +
            //                 '" data-logo="' +
            //                 TapSNDRUtils.getPaymentMethodLogo(paymentDetails.method) +
            //                 '" data-subcontent="' +
            //                 (paymentDetails.account_name ? paymentDetails.tag || paymentDetails.email || paymentDetails.phone_number : "") +
            //                 '">' +
            //                 (paymentDetails.account_name || paymentDetails.tag || paymentDetails.email || paymentDetails.phone_number) +
            //                 "</option>"
            //         );
            //     }
            //     const optionFormat = (item) => {
            //         if (!item.id) {
            //             return item.text;
            //         }
            //         const span = document.createElement("span");
            //         let template = "";
            //         template += '<div class="d-flex align-items-center">';
            //         template += '<img src="' + item.element.getAttribute("data-logo") + '" class="h-40px me-3" alt=""/>';
            //         template += '<div class="d-flex flex-column">';
            //         template += '<span class="fs-4 fw-bold lh-1">' + item.text + "</span>";
            //         template += '<span class="text-muted fs-5">' + item.element.getAttribute("data-subcontent") + "</span>";
            //         template += "</div>";
            //         template += "</div>";
            //         span.innerHTML = template;
            //         return $(span);
            //     };
            //     $(selectors.controls.payment_details_id.self).select2({
            //         placeholder: "Select an option",
            //         minimumResultsForSearch: Infinity,
            //         templateSelection: optionFormat,
            //         templateResult: optionFormat,
            //     });
            // };

            const setEvents = () => {
                for (const key in selectors.controls) {
                    $(typeof selectors.controls[key] === "object" ? selectors.controls[key].self : selectors.controls[key]).on("change", function () {
                        ref.originalData = { ...states.data };
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
                ref.originalData = { ...states.data };
                states.data = data;
                onDataChanged();
            };

            const setDomain = (domain) => {
                states.domain = domain;
                onDomainChanged();
            };

            const submit = () => {
                $(selectors.self).submit();
            };

            return {
                init: ({ onSubmit }) => {
                    props.onSubmit = onSubmit;
                    setFormValidation();
                    setEvents();

                    // getPaymentDetails((paymentMethods) => {
                    //     states.paymentDetails = paymentMethods;
                    //     onPaymentDetailsChanged();
                    // });
                },
                setData,
                setDomain,
                submit,
            };
        };

        return {
            getInstance,
        };
    })();
})();
