(() => {
    if (window.TapSNDRClientModal) return;
    window.TapSNDRClientModal = (() => {
        const partId = "tapsndr-modal-client";

        const getInstance = (assignedId) => {
            const selectors = (() => {
                const self = "." + partId + "." + assignedId;
                return {
                    self,
                    title: self + " .modal-title",
                };
            })();

            let statsSection = null;

            const show = () => {
                $(selectors.self).modal("show");
            };

            const hide = () => {
                $(selectors.self).modal("hide");
            };

            const setTitle = (title) => {
                $(selectors.title).html(title);
            };

            const setData = (data) => {
                statsSection.setData(data.stats);
            };

            return {
                init: () => {
                    statsSection = TapSNDRClientStats.getInstance(partId + "-" + assignedId + "-stats");
                    statsSection.init();
                },
                show,
                hide,
                setTitle,
                setData,
            };
        };

        return {
            getInstance,
        };
    })();
})();
