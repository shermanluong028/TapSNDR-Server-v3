(() => {
    if (window.TapSNDRPaymentDetailsForm) return;
    window.TapSNDRPaymentDetailsForm = (() => {
        const partId = "tapsndr-form-payment_details";

        const getPaymentMethodItemHTML = (paymentMethod) => {
            return (
                '<div class="col-3">' +
                '<label class="form-check-image">' +
                '<div class="form-check-wrapper border-0">' +
                '<img src="' +
                TapSNDRUtils.getPaymentMethodLogo(paymentMethod) +
                '" />' +
                "</div>" +
                '<div class="form-check form-check-custom form-check-solid">' +
                '<input class="form-check-input" type="radio" value="' +
                paymentMethod.id +
                '" name="method_id" />' +
                '<div class="form-check-label">' +
                paymentMethod.method_name +
                "</div>" +
                "</div>" +
                "</label>" +
                "</div>"
            );
        };

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    paymentMethods: self + " ." + partId + "-payment_methods",
                    controls: {
                        method_id: self + " input[name='method_id']",
                        tag: {
                            self: self + " input[name='tag']",
                            group: self + " div.form-group:has( > input[name='tag'])",
                            label: self + " div.form-group:has(input[name='tag']) > label",
                        },
                        email: {
                            self: self + " input[name='email']",
                            group: self + " div.form-group:has( > input[name='email'])",
                        },
                        phone_number: {
                            self: self + " input[name='phone_number']",
                            group: self + " div.form-group:has( > input[name='phone_number'])",
                            label: self + " div.form-group:has( > input[name='phone_number']) > label",
                        },
                        email_or_phone: {
                            self: self + " input[name='email_or_phone']",
                            group: self + " div.form-group:has( > input[name='email_or_phone'])",
                            label: self + " div.form-group:has( > input[name='email_or_phone']) > label",
                        },
                        account_name: {
                            self: self + " input[name='account_name']",
                            group: self + " div.form-group:has( > input[name='account_name'])",
                        },
                        qrcode: {
                            self: self + " input[name='qrcode']",
                            group: self + " div.form-group:has(input[name='qrcode'])",
                            label: self + " div.form-group:has(input[name='qrcode']) > label",
                        },
                    },
                };
            })();

            let qrcodeInput = null;

            const props = {};
            const states = {
                data: null,
                paymentMethods: null,
                mode: "edit",
            };
            const ref = {
                initialData: null,
                originalData: null,
            };

            const setFormValidation = () => {
                $.validator.addMethod(
                    "phone",
                    function (val, el) {
                        return this.optional(el) || TapSNDRUtils.phoneRegex.test(val);
                    },
                    "Please enter a valid phone number."
                );
                $.validator.addMethod(
                    "emailOrPhone",
                    function (val, el) {
                        return this.optional(el) || TapSNDRUtils.emailRegex.test(val) || TapSNDRUtils.phoneRegex.test(val);
                    },
                    "Please enter a valid email address or phone number."
                );
                $(selectors.self).validate({
                    errorClass: "text-danger",
                    rules: {
                        method_id: {
                            required: true,
                        },
                        tag: {
                            required: true,
                        },
                        email: {
                            required: true,
                            email: true,
                        },
                        phone_number: {
                            required: true,
                            phone: true,
                        },
                        account_name: {
                            required: true,
                        },
                    },
                    messages: {
                        method_id: {
                            required: "Select an option.",
                        },
                    },
                    errorPlacement: (err, el) => {
                        if (el.attr("name") === "method_id") {
                            err.appendTo(selectors.paymentMethods);
                        } else if (el.attr("name") === "qrcode") {
                            err.appendTo(selectors.controls.qrcode.group);
                        } else {
                            err.insertAfter(el);
                        }
                    },
                    submitHandler: function (_, e) {
                        e.preventDefault();
                        const data = { ...states.data };
                        if (data.email_or_phone) {
                            if (TapSNDRUtils.emailRegex.test(data.email_or_phone)) {
                                data.email = data.email_or_phone;
                                data.phone_number = "";
                            } else {
                                data.email = "";
                                data.phone_number = data.email_or_phone;
                            }
                            delete data.email_or_phone;
                        }
                        const formData = new FormData();
                        for (const key in data) {
                            if (key === "id" || ref.initialData?.[key] != data[key]) {
                                if (key === "qrcode_url" && !data[key]) {
                                    formData.append(key, "null");
                                } else {
                                    formData.append(key, data[key]);
                                }
                            }
                        }
                        const qrcodeImage = qrcodeInput.getImage();
                        if (qrcodeImage) {
                            formData.delete("qrcode_url");
                            formData.append("qrcodeFile", qrcodeImage);
                        }
                        props.onSubmit(formData);
                    },
                });
            };

            const getPaymentMethods = (cb) => {
                TapSNDRUtils.ajax("get", serverUrl + "/web/payment_methods", (success, data, error) => {
                    if (!success) {
                        TapSNDRUtils.toast("error", error);
                        return;
                    }
                    cb(data);
                });
            };

            const onPaymentMethodsChanged = () => {
                $(selectors.paymentMethods).empty();
                for (let i = 0; i < states.paymentMethods.length; i++) {
                    const paymentMethod = states.paymentMethods[i];
                    $(selectors.paymentMethods).append(getPaymentMethodItemHTML(paymentMethod));
                }
                KTImageInput.createInstances();
                setEvents();
            };

            const onDataChanged = () => {
                if (ref.originalData?.method_id !== states.data?.method_id) {
                    ref.originalData = { ...states.data };
                    if (ref.initialData?.method_id == states.data?.method_id) {
                        states.data = { ...ref.initialData };
                    } else {
                        states.data = _.pick(states.data, ["id", "method_id"]);
                    }
                    $(selectors.self).validate().resetForm();
                    // qrcodeInput.reset();
                    onDataChanged();
                    return;
                }

                $(selectors.controls.method_id).prop("checked", false);
                $(selectors.controls.method_id + '[value="' + states.data?.method_id + '"]').prop("checked", true);
                $(selectors.controls.tag.self).val(states.data?.tag || "");
                $(selectors.controls.email.self).val(states.data?.email || "");
                $(selectors.controls.phone_number.self).val(states.data?.phone_number || "");
                $(selectors.controls.email_or_phone.self).val(
                    (states.data?.email_or_phone !== undefined ? states.data.email_or_phone : states.data?.email || states.data?.phone_number) || ""
                );
                $(selectors.controls.account_name.self).val(states.data?.account_name || "");
                qrcodeInput.setImageURL(states.data?.qrcode_url);

                $(selectors.controls.tag.group).hide();
                $(selectors.controls.tag.label).html("@");
                $(selectors.controls.email.group).hide();
                $(selectors.controls.phone_number.group).hide();
                $(selectors.controls.email_or_phone.group).hide();
                $(selectors.controls.email_or_phone.label).addClass("required");
                $(selectors.controls.email_or_phone.self).rules("add", {
                    required: true,
                    emailOrPhone: true,
                });
                $(selectors.controls.account_name.group).hide();
                $(selectors.controls.qrcode.group).hide();
                $(selectors.controls.qrcode.label).addClass("required");
                if (!states.data?.qrcode_url) {
                    $(selectors.controls.qrcode.self).rules("add", {
                        required: true,
                        messages: {
                            required: "Choose an image.",
                        },
                    });
                } else {
                    $(selectors.controls.qrcode.self).rules("remove", "required");
                }

                const paymentMethod = states.data?.method || _.find(states.paymentMethods, { id: Number(states.data?.method_id || 0) });

                if (paymentMethod?.method_name.replace(/\s+/g, "").includes("CashApp")) {
                    // Cash App
                    $(selectors.controls.tag.group).show();
                    $(selectors.controls.email_or_phone.group).show();
                    $(selectors.controls.email_or_phone.label).html("Cash App Email or Phone number").removeClass("required");
                    $(selectors.controls.email_or_phone.self).rules("remove", "required");
                    $(selectors.controls.tag.label).html("$cashtag");
                    $(selectors.controls.account_name.group).show();
                    $(selectors.controls.qrcode.group).show();
                } else if (paymentMethod?.method_name.includes("Zelle")) {
                    // Zelle
                    $(selectors.controls.email_or_phone.group).show();
                    $(selectors.controls.email_or_phone.label).html("Zelle Email or Phone number");
                    $(selectors.controls.account_name.group).show();
                    $(selectors.controls.qrcode.group).show();
                    $(selectors.controls.qrcode.label).removeClass("required");
                    $(selectors.controls.qrcode.self).rules("remove", "required");
                } else if (paymentMethod?.method_name.includes("Chime")) {
                    // Chime
                    $(selectors.controls.tag.group).show();
                    $(selectors.controls.tag.label).html("$chimetag");
                    $(selectors.controls.email_or_phone.group).show();
                    $(selectors.controls.email_or_phone.label).html("Chime Email or Phone number");
                    $(selectors.controls.account_name.group).show();
                    $(selectors.controls.qrcode.group).show();
                } else if (paymentMethod?.method_name.includes("PayPal")) {
                    // PayPal
                    $(selectors.controls.tag.group).show();
                    $(selectors.controls.email_or_phone.group).show();
                    $(selectors.controls.email_or_phone.label).html("PayPal Email or Phone number");
                    $(selectors.controls.account_name.group).show();
                    $(selectors.controls.qrcode.group).show();
                } else if (paymentMethod?.method_name.replace(/\s+/g, "").includes("ApplePay")) {
                    // Apple Pay
                    $(selectors.controls.phone_number.group).show();
                    $(selectors.controls.phone_number.label).html("Apple Pay Phone number");
                } else if (paymentMethod?.method_name.includes("Venmo")) {
                    // Venmo
                    $(selectors.controls.tag.group).show();
                    $(selectors.controls.phone_number.group).show();
                    $(selectors.controls.phone_number.label).html("Venmo Phone number");
                    $(selectors.controls.account_name.group).show();
                    $(selectors.controls.qrcode.group).show();
                } else if (paymentMethod?.method_name.includes("Skrill")) {
                    // Skrill
                    $(selectors.controls.email_or_phone.group).show();
                    $(selectors.controls.email_or_phone.label).html("Skrill Email or Phone number");
                    $(selectors.controls.qrcode.group).show();
                }
            };

            const onModeChanged = () => {
                if (states.mode === "view") {
                    for (const key in selectors.controls) {
                        $(typeof selectors.controls[key] === "object" ? selectors.controls[key].self : selectors.controls[key]).attr("disabled", true);
                    }
                    $(selectors.paymentMethods).hide();
                }
            };

            const setEvents = () => {
                for (const key in selectors.controls) {
                    if (key === "qrcode") {
                        continue;
                    }
                    $(typeof selectors.controls[key] === "object" ? selectors.controls[key].self : selectors.controls[key])
                        .off("change")
                        .on("change", function () {
                            ref.originalData = { ...states.data };
                            if (!states.data) {
                                states.data = {};
                            }
                            states.data[$(this).attr("name")] = $(this).val();
                            onDataChanged();
                        });
                }
            };

            const onQRCodeInputChanged = (url) => {
                ref.originalData = { ...states.data };
                states.data.qrcode_url = url;
                onDataChanged();
            };

            const setData = (data) => {
                $(selectors.self).validate().resetForm();
                qrcodeInput.reset();
                ref.initialData = { ...data };
                ref.originalData = { ...states.data };
                states.data = { ...data };
                onDataChanged();
            };

            const setMode = (mode) => {
                states.mode = mode;
                onModeChanged();
            };

            const submit = () => {
                $(selectors.self).submit();
            };

            return {
                init: ({ onSubmit }) => {
                    props.onSubmit = onSubmit;

                    qrcodeInput = TapSNDRImageInput.getInstance(partId + "-" + assignedId + "-qrcode");
                    qrcodeInput.init({
                        onChange: onQRCodeInputChanged,
                    });

                    setFormValidation();
                    setEvents();
                    getPaymentMethods((paymentMethods) => {
                        states.paymentMethods = paymentMethods;
                        onPaymentMethodsChanged();
                    });
                },
                setData,
                setMode,
                submit,
            };
        };

        return {
            getInstance,
        };
    })();
})();
