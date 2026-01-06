window.TapSNDRPage = (function () {
    const pageId = "tapsndr-signin";

    const selectors = {
        form: {
            self: "." + pageId + "-form",
            alerts: {
                success: "." + pageId + "-alert-success",
            },
            buttons: {
                signin: "." + pageId + "-btn-signin",
            },
        },
    };

    const setFormValidation = function () {
        $(selectors.form.self).validate({
            errorClass: "text-danger",
            rules: {
                username: {
                    required: true,
                },
                password: {
                    required: true,
                    // minlength: 6,
                },
            },
            submitHandler: function (element, event) {
                event.preventDefault();
                $(selectors.form.buttons.signin)
                    .html('Please wait...<span class="spinner-border spinner-border-sm align-middle ms-2"></span>')
                    .attr("disabled", true);
                TapSNDRUtils.ajax("post", serverUrl + "/web/auth/signin", TapSNDRUtils.getFormData(element), (success, _, error) => {
                    $(selectors.form.buttons.signin).html("Continue").attr("disabled", false);
                    if (!success) {
                        TapSNDRUtils.toast("error", error);
                        return;
                    }
                    $(selectors.form.alerts.success).removeClass("d-none");
                    const searchParams = new URLSearchParams(window.location.search);
                    const redirectTo = searchParams.get("redirect_to");
                    window.location.href = redirectTo || serverUrl + "/";
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
