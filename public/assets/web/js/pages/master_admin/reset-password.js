window.TapSNDRPage = (function () {
    const pageId = "tapsndr-reset_password";

    const selectors = {
        form: (() => {
            const self = "." + pageId + "-form";
            return {
                self,
                alerts: {
                    success: "." + pageId + "-alert-success",
                    could_not_reset: "." + pageId + "-alert-could_not_reset",
                },
                buttons: {
                    submit: "." + pageId + "-btn-submit",
                },
                controls: {
                    password: {
                        self: self + " input[name='password']",
                        group: self + " div.fv-row:has(input[name='password'])",
                    },
                },
            };
        })(),
    };

    const hideAlerts = function () {
        for (const key in selectors.form.alerts) {
            $(selectors.form.alerts[key]).addClass("d-none");
        }
    };

    const setFormValidation = function () {
        $(selectors.form.self).validate({
            errorClass: "text-danger",
            rules: {
                email: {
                    required: true,
                    email: true,
                },
                password: {
                    required: true,
                    // minlength: 6,
                },
                password1: {
                    required: true,
                    equalTo: $(selectors.form.controls.password.self),
                },
            },
            errorPlacement: (err, el) => {
                if (el.attr("name") === "password") {
                    err.appendTo(selectors.form.controls.password.group);
                } else {
                    err.insertAfter(el);
                }
            },
            submitHandler: function (element, event) {
                event.preventDefault();
                $(selectors.form.buttons.submit)
                    .html('Please wait...<span class="spinner-border spinner-border-sm align-middle ms-2"></span>')
                    .attr("disabled", true);
                TapSNDRUtils.ajax("post", serverUrl + "/web/reset-password", TapSNDRUtils.getFormData(element), (success, _, error) => {
                    $(selectors.form.buttons.submit).html("Submit").attr("disabled", false);
                    if (!success) {
                        if (error === "Could not reset the password") {
                            hideAlerts();
                            $(selectors.form.alerts.could_not_reset).removeClass("d-none");
                        } else {
                            TapSNDRUtils.toast("error", error);
                        }
                        return;
                    }
                    hideAlerts();
                    $(selectors.form.alerts.success).removeClass("d-none");
                });
            },
        });
    };

    return {
        init: function () {
            setFormValidation();
        },
    };
})();
