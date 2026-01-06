window.TapSNDRApp = (() => {
    return {
        init: () => {
            if (window.accounting) {
                accounting.settings.currency.format = {
                    pos: "%s%v",
                    neg: "-%s%v",
                    zero: "%s --",
                };
            }
            if (window.TapSNDRHeader) {
                TapSNDRHeader.init();
            }
            if (window.TapSNDRMenu) {
                TapSNDRMenu.init();
            }
            if (window.TapSNDRPage) {
                TapSNDRPage.init();
            }
        },
    };
})();

$(document).ready(() => {
    TapSNDRApp.init();
});
