window.TapSNDRHeader = (function () {
    const partId = "tapsndr-header";

    const selectors = {
        buttons: {
            signout: "." + partId + "-signout",
        },
    };

    const onSignOut = () => {
        TapSNDRUtils.ajax(
            "post",
            serverUrl + "/web/auth/signout",
            { _token: csrf_token },
            (success, _, error) => {
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                window.location.href = "/";
            }
        );
    };

    const setEvents = () => {
        $(selectors.buttons.signout).on("click", onSignOut);
    };

    return {
        init: () => {
            setEvents();
        },
    };
})();
