(() => {
    if (window.TapSNDRUserForm) return;
    window.TapSNDRUserForm = (() => {
        const partId = "tapsndr-form-user";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    // commission_percentage: self + " ." + partId + "-commission_percentage",
                    controls: {
                        id: self + " input[name='id']",
                        username: self + " input[name='username']",
                        email: self + " input[name='email']",
                        phone: self + " input[name='phone']",
                        role: self + " select[name='role']",
                        // domains: {
                        //     self: self + " input[name='domains']",
                        //     group:
                        //         self +
                        //         " div.form-group:has( > input[name='domains'])",
                        // },
                        password: {
                            self: self + " input[name='password']",
                            group: self + " div.form-group:has( > input[name='password'])",
                        },
                        password1: {
                            self: self + " input[name='password1']",
                            group: self + " div.form-group:has( > input[name='password1'])",
                        },
                        // admin_client_commission_percentage: {
                        //     self: self + " input[name='admin_client_commission_percentage']",
                        //     group: self + " div.form-group:has( > input[name='admin_client_commission_percentage'])",
                        // },
                        // admin_customer_commission_percentage: {
                        //     self: self + " input[name='admin_customer_commission_percentage']",
                        //     group: self + " div.form-group:has( > input[name='admin_customer_commission_percentage'])",
                        // },
                        // distributor_client_commission_percentage: {
                        //     self: self + " input[name='distributor_client_commission_percentage']",
                        //     group: self + " div.form-group:has( > input[name='distributor_client_commission_percentage'])",
                        // },
                        // distributor_customer_commission_percentage: {
                        //     self: self + " input[name='distributor_customer_commission_percentage']",
                        //     group: self + " div.form-group:has( > input[name='distributor_customer_commission_percentage'])",
                        // },
                    },
                };
            })();

            const props = {};
            const states = {};

            const setFormValidation = () => {
                // $.validator.addMethod(
                //     "domains",
                //     function (value) {
                //         if (!value) {
                //             return true;
                //         }
                //         let domains = JSON.parse(value);
                //         for (let i = 0; i < domains.length; i++) {
                //             if (
                //                 !/^[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.[a-zA-Z]{2,}$/.test(
                //                     domains[i].value
                //                 )
                //             ) {
                //                 return false;
                //             }
                //         }
                //         return true;
                //     },
                //     "Please list only domains."
                // );
                $(selectors.self).validate({
                    errorClass: "text-danger",
                    rules: {
                        username: {
                            required: true,
                        },
                        email: {
                            email: true,
                        },
                        phone: {
                            digits: true,
                        },
                        role: {
                            required: true,
                        },
                        // domains: {
                        //     required: true,
                        //     domains: true,
                        // },
                        // admin_client_commission_percentage: {
                        //     required: true,
                        //     min: 0,
                        //     max: 10,
                        // },
                        // admin_customer_commission_percentage: {
                        //     required: true,
                        //     min: 0,
                        //     max: 10,
                        // },
                        // distributor_client_commission_percentage: {
                        //     required: true,
                        //     min: 0,
                        //     max: 10,
                        // },
                        // distributor_customer_commission_percentage: {
                        //     required: true,
                        //     min: 0,
                        //     max: 10,
                        // },
                        password1: {
                            equalTo: selectors.controls.password.self,
                        },
                    },
                    submitHandler: function (el, e) {
                        e.preventDefault();
                        const data = TapSNDRUtils.getFormData(el);
                        // if (data.domains) {
                        //     data.domains = JSON.parse(data.domains).map(
                        //         (item) => item.value
                        //     );
                        // }
                        // if (!data.id) {
                        //     data.password = TapSNDRUtils.getRandomPassword();
                        // }
                        // if (data.role === "user") {
                        //     data.commission_percentage = {};
                        //     if (data.admin_client_commission_percentage) {
                        //         data.commission_percentage.admin_client = data.admin_client_commission_percentage;
                        //     }
                        //     if (data.admin_customer_commission_percentage) {
                        //         data.commission_percentage.admin_customer = data.admin_customer_commission_percentage;
                        //     }
                        //     if (data.distributor_client_commission_percentage) {
                        //         data.commission_percentage.distributor_client = data.distributor_client_commission_percentage;
                        //     }
                        //     if (data.distributor_customer_commission_percentage) {
                        //         data.commission_percentage.distributor_customer = data.distributor_customer_commission_percentage;
                        //     }
                        // }
                        // delete data.admin_client_commission_percentage;
                        // delete data.admin_customer_commission_percentage;
                        // delete data.distributor_client_commission_percentage;
                        // delete data.distributor_customer_commission_percentage;
                        props.onSubmit(data);
                    },
                });
            };

            const getRoles = () => {
                TapSNDRUtils.ajax("get", serverUrl + "/web/roles", (success, data, error) => {
                    if (!success) {
                        TapSNDRUtils.toast("error", error);
                        return;
                    }
                    states.roles = data;
                    onRolesChanged();
                });
            };

            const onRolesChanged = () => {
                $(selectors.controls.role).html("");
                states.roles.forEach((role) => {
                    $(selectors.controls.role).append("<option value=" + role.name + ">" + (TapSNDRUtils.mapLabelToRole[role.name] || role.name) + "</option>");
                });
            };

            const onDataChanged = () => {
                if (states.data?.roles?.[0]?.name) {
                    states.data.role = states.data.roles[0].name;
                    delete states.data.roles;
                }

                // if (states.data?.commission_percentage) {
                //     states.data.admin_client_commission_percentage = states.data.commission_percentage.admin_client;
                //     states.data.admin_customer_commission_percentage = states.data.commission_percentage.admin_customer;
                //     states.data.distributor_client_commission_percentage = states.data.commission_percentage.distributor_client;
                //     states.data.distributor_customer_commission_percentage = states.data.commission_percentage.distributor_customer;
                //     delete states.data.commission_percentage;
                // }

                $(selectors.controls.id).val(states.data?.id || "");
                $(selectors.controls.username).val(states.data?.username || "");
                $(selectors.controls.email).val(states.data?.email || "");
                $(selectors.controls.phone).val(states.data?.phone || "");
                // $(selectors.controls.domains.self).val(
                //     states.data?.domains || ""
                // );
                $(selectors.controls.role).val(states.data?.role || "");
                // $(selectors.controls.admin_client_commission_percentage.self).val(states.data?.admin_client_commission_percentage || "");
                // $(selectors.controls.admin_customer_commission_percentage.self).val(states.data?.admin_customer_commission_percentage || "");
                // $(selectors.controls.distributor_client_commission_percentage.self).val(states.data?.distributor_client_commission_percentage || "");
                // $(selectors.controls.distributor_customer_commission_percentage.self).val(states.data?.distributor_customer_commission_percentage || "");

                if (states.data?.id) {
                    // $(selectors.controls.password.group).show();
                    // $(selectors.controls.password1.group).show();
                    $(selectors.controls.password.self).rules("remove", "required");
                    $(selectors.controls.password1.self).rules("remove", "required");
                } else {
                    // $(selectors.controls.password.group).hide();
                    // $(selectors.controls.password1.group).hide();
                    $(selectors.controls.password.self).rules("add", {
                        required: true,
                    });
                    $(selectors.controls.password1.self).rules("add", {
                        required: true,
                    });
                }

                // if (states.data?.role === "user") {
                //     // $(selectors.controls.domains.group).show();
                //     $(selectors.commission_percentage).show();
                //     if (TapSNDRCurrentUser.role === "distributor") {
                //         $(selectors.controls.admin_client_commission_percentage.group).hide();
                //         $(selectors.controls.admin_customer_commission_percentage.group).hide();
                //     }
                // } else {
                //     // $(selectors.controls.domains.group).hide();
                //     $(selectors.commission_percentage).hide();
                // }
            };

            // const setTagify = () => {
            //     new Tagify(
            //         document.querySelector(selectors.controls.domains.self)
            //     );
            // };

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
                    // setTagify();
                    setEvents();
                    getRoles();
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
