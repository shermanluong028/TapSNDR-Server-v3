(() => {
    if (window.TapSNDRImageInput !== undefined) return;
    window.TapSNDRImageInput = (function () {
        const partId = "tapsndr-image_input";
        const getInstance = (assignedId) => {
            const selectors = {
                self: "." + partId + "." + assignedId,
            };

            const props = {};

            const ref = {
                ktImageInput: null,
            };

            const setEvents = () => {
                ref.ktImageInput.on("kt.imageinput.changed", function () {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        props.onChange(e.target.result);
                    };
                    reader.readAsDataURL(ref.ktImageInput.inputElement.files[0]);
                });
                ref.ktImageInput.on("kt.imageinput.removed", function () {
                    props.onChange(null);
                });
            };

            const setImageURL = (url) => {
                if (url) {
                    $(ref.ktImageInput.element).removeClass("image-input-empty");
                    ref.ktImageInput.wrapperElement.style.backgroundImage = 'url("' + url + '")';
                    ref.ktImageInput.wrapperElement.style.cursor = "pointer";
                    ref.ktImageInput.wrapperElement.onclick = function () {
                        window.open(url, "_blank");
                    };
                } else {
                    $(ref.ktImageInput.element).addClass("image-input-empty");
                    ref.ktImageInput.wrapperElement.style.backgroundImage = "none";
                }
            };

            const getImage = () => {
                const imageInput = ref.ktImageInput.getInputElement();
                return imageInput.files[0];
            };

            const reset = () => {
                ref.ktImageInput.cancelElement.click();
            };

            return {
                init: ({ onChange }) => {
                    props.onChange = onChange;
                    ref.ktImageInput = KTImageInput.getInstance(document.querySelector(selectors.self));
                    setEvents();
                },
                setImageURL,
                getImage,
                reset,
            };
        };
        return {
            getInstance,
        };
    })();
})();
