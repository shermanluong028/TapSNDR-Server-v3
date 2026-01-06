window.TapSNDRPage = (function () {
    const pageId = "tapsndr-signup";

    const selectors = {
        form: (() => {
            const self = "." + pageId + "-form";
            return {
                self,
                alerts: {
                    success: "." + pageId + "-alert-success",
                },
                buttons: {
                    signup: "." + pageId + "-btn-signup",
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

    const setFormValidation = function () {
        $(selectors.form.self).validate({
            errorClass: "text-danger",
            rules: {
                email: {
                    required: true,
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
                $(selectors.form.buttons.signup)
                    .html('Please wait...<span class="spinner-border spinner-border-sm align-middle ms-2"></span>')
                    .attr("disabled", true);
                TapSNDRUtils.ajax("post", serverUrl + "/web/auth/signup", TapSNDRUtils.getFormData(element), (success, _, error) => {
                    $(selectors.form.buttons.signup).html("Continue").attr("disabled", false);
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
