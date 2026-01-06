window.TapSNDRPage = (function () {
    const pageId = "tapsndr-forgot_password";

    const selectors = {
        form: {
            self: "." + pageId + "-form",
            alerts: {
                success: "." + pageId + "-alert-success",
                user_not_found: "." + pageId + "-alert-user_not_found",
                could_not_send_email: "." + pageId + "-alert-could_not_send_email",
            },
            buttons: {
                submit: "." + pageId + "-btn-submit",
            },
        },
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
                },
            },
            submitHandler: function (element, event) {
                event.preventDefault();
                $(selectors.form.buttons.submit)
                    .html('Please wait...<span class="spinner-border spinner-border-sm align-middle ms-2"></span>')
                    .attr("disabled", true);
                TapSNDRUtils.ajax("post", serverUrl + "/web/forgot-password", TapSNDRUtils.getFormData(element), (success, _, error) => {
                    $(selectors.form.buttons.submit).html("Submit").attr("disabled", false);
                    if (!success) {
                        if (error === "User Not Found") {
                            hideAlerts();
                            $(selectors.form.alerts.user_not_found).removeClass("d-none");
                        } else if (error === "Could not send the password reset link") {
                            hideAlerts();
                            $(selectors.form.alerts.could_not_send_email).removeClass("d-none");
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
