window.TapSNDRMenu = (function () {
    const partId = "tapsndr-menu";

    // const selectors = {
    //     items: {
    //         //
    //     },
    // };

    // const states = {
    //     //
    // };

    const setActive = (menuItem) => {
        $("." + partId + "-" + menuItem).addClass("show");
    };

    return {
        init: () => {
            //
        },
        setActive,
    };
})();
