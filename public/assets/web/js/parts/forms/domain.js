(() => {
    if (window.TapSNDRDomainForm) return;
    window.TapSNDRDomainForm = (() => {
        const partId = "tapsndr-form-domain";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    controls: {
                        id: self + " input[name='id']",
                        vendor_code: self + " input[name='vendor_code']",
                        description: self + " input[name='group_name']",
                        games: self + " input[name='games']",
                        telegram_chat_id: self + " input[name='telegram_chat_id']",
                        client_id: self + " select[name='client_id']",
                    },
                };
            })();

            let gamesTagify = null;

            const props = {};
            const states = {
                clients: null,
                games: null,
            };

            const setFormValidation = () => {
                $(selectors.self).validate({
                    errorClass: "text-danger",
                    rules: {
                        vendor_code: {
                            required: true,
                        },
                        group_name: {
                            required: true,
                        },
                        telegram_chat_id: {
                            required: true,
                        },
                    },
                    submitHandler: function (el, e) {
                        e.preventDefault();
                        const data = TapSNDRUtils.getFormData(el);
                        data.games = gamesTagify.value.map((tag) => tag.value);
                        props.onSubmit(data);
                    },
                });
            };

            const getClients = () => {
                TapSNDRUtils.ajax(
                    "get",
                    serverUrl + "/web/users",
                    {
                        role: "user",
                    },
                    (success, data, error) => {
                        if (!success) {
                            TapSNDRUtils.toast("error", error);
                            return;
                        }
                        states.clients = data;
                        onClientsChanged();
                    }
                );
            };

            const getGames = (cb) => {
                TapSNDRUtils.ajax("get", serverUrl + "/web/games", (success, data, error) => {
                    if (!success) {
                        TapSNDRUtils.toast("error", error);
                        return;
                    }
                    cb(data);
                });
            };

            const onClientsChanged = () => {
                $(selectors.controls.client_id).html("<option value=''>N/A</option>");
                for (let i = 0; i < states.clients.length; i++) {
                    const client = states.clients[i];
                    $(selectors.controls.client_id).append("<option value=" + client.id + ">" + client.username + " #" + client.id + "</option>");
                }
            };

            const onDataChanged = () => {
                if (states.data?.games === undefined || Array.isArray(states.data?.games)) {
                    gamesTagify.removeAllTags();
                    gamesTagify.addTags(
                        (states.data?.games || []).map((game) => ({
                            value: game.game_name,
                        }))
                    );
                }
                $(selectors.controls.id).val(states.data?.id || "");
                $(selectors.controls.vendor_code).val(states.data?.vendor_code || "");
                $(selectors.controls.description).val(states.data?.group_name || "");
                $(selectors.controls.telegram_chat_id).val(states.data?.telegram_chat_id || "");
                $(selectors.controls.client_id).val(states.data?.client_id || "");
            };

            function getRandomColor() {
                function rand(min, max) {
                    return min + Math.random() * (max - min);
                }

                var h = rand(1, 360) | 0,
                    s = rand(40, 70) | 0,
                    l = rand(65, 72) | 0;

                return "hsl(" + h + "," + s + "%," + l + "%)";
            }

            const setGamesTagify = () => {
                gamesTagify = new Tagify(document.querySelector(selectors.controls.games), {
                    placeholder: "Enter game names",
                    dropdown: {
                        maxItems: Infinity,
                        enabled: 0,
                        closeOnSelect: false,
                    },
                    transformTag(tagData) {
                        tagData.style =
                            "--tag-bg: var(--bs-danger); --tag-hover: var(--bs-danger); --tag-text-color: var(--bs-danger-inverse); --tag-remove-bg: var(--tag-hover);";
                    },
                });
                getGames((games) => {
                    gamesTagify.settings.whitelist = games.map((game) => ({
                        value: game.game_name,
                    }));
                });
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
                $(selectors.self).trigger("submit");
            };

            return {
                init: ({ onSubmit }) => {
                    props.onSubmit = onSubmit;
                    setFormValidation();
                    setGamesTagify();
                    setEvents();
                    getClients();
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
