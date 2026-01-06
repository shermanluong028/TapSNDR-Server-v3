window.TapSNDRUtils = (() => {
    const isEmpty = (value) => {
        if (
            value === undefined ||
            value === null ||
            (typeof value === "string" && value.length === 0) ||
            (typeof value === "object" && Object.keys(value) === 0)
        ) {
            return true;
        }
        return false;
    };

    const ajax = (type, url, data, fileUpload, callback) => {
        if (typeof data === "function") {
            callback = data;
            data = null;
        }

        if (typeof fileUpload === "function") {
            callback = fileUpload;
            fileUpload = false;
        }

        if (type !== "GET" && type !== "get" && type !== "POST" && type !== "post" && type !== "DELETE" && type !== "delete") {
            callback(false, null, "Invalid ajax option.");
        }

        $.ajax({
            type,
            url,
            data,
            dataType: "json",
            // async: false,
            processData: fileUpload ? false : true,
            contentType: fileUpload ? false : "application/x-www-form-urlencoded",
            success: function (response) {
                if (Number(response.status) === 1) {
                    callback(true, response.data);
                } else {
                    callback(false, null, response.error);
                }
            },
            error: function (err) {
                callback(false, null, err.responseJSON?.error || err.responseJSON?.message || err.responseText || err.statusText || "Request failed");
            },
        });
    };

    const alert = (type, html, cb, options) => {
        if (type !== "success" && type !== "error" && type !== "warning" && type !== "info" && type !== "question") {
            options = cb;
            cb = html;
            html = type;
            type = "info";
        }

        if (typeof cb === "object") {
            options = cb;
            cb = null;
        }

        Swal.fire({
            html,
            icon: type,
            buttonsStyling: false,
            confirmButtonText: options?.confirmButtonText || (type === "question" ? "Yes" : "Ok"),
            cancelButtonText: options?.cancelButtonText || "No",
            showConfirmButton: options?.showConfirmButton !== false,
            showCancelButton: options?.showCancelButton || type === "question",
            allowOutsideClick: !!options?.allowOutsideClick,
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-light",
            },
        }).then((eventStatus) => {
            if (typeof cb === "function") {
                cb(eventStatus);
            }
        });
    };

    const toast = (type, title, message) => {
        toastr.options = {
            closeButton: false,
            debug: false,
            newestOnTop: false,
            progressBar: true,
            positionClass: "toastr-top-right",
            preventDuplicates: false,
            onclick: null,
            showDuration: "300",
            hideDuration: "1000",
            timeOut: "5000",
            extendedTimeOut: "1000",
            showEasing: "swing",
            hideEasing: "linear",
            showMethod: "fadeIn",
            hideMethod: "fadeOut",
        };
        toastr[type](message, title);
    };

    const getFormData = (formElem) => {
        const formData = {};
        const serializedData = $(formElem).serializeArray();
        serializedData.forEach((dataObj) => {
            formData[dataObj.name] = dataObj.value;
        });
        return formData;
    };

    function PN2(value) {
        return Number(value.toFixed(2));
    }

    function PN10(value) {
        return Number(value.toFixed(10));
    }

    const showLoading = () => {
        const loadingEl = document.createElement("div");
        document.body.prepend(loadingEl);
        loadingEl.classList.add("page-loader");
        loadingEl.classList.add("flex-column");
        loadingEl.classList.add("bg-dark");
        loadingEl.classList.add("bg-opacity-25");
        loadingEl.innerHTML = '<span class="spinner-border text-primary" role="status">';
        loadingEl.innerHTML += '<span className="text-gray-800 fs-6 fw-semibold mt-5">Loading...</span>';
        KTApp.showPageLoading();
    };

    const hideLoading = () => {
        KTApp.hidePageLoading();
        document.querySelector(".page-loader").remove();
    };

    const getIDHTML = (id) => {
        return "#" + id;
    };

    const getUsernameHTML = (username) => {
        return "@" + username;
    };

    const getDateHTML = (date) => {
        if (!date) {
            return "-";
        }
        return moment(date).format("YYYY/MM/DD HH:mm:ss").replaceAll(" ", "&nbsp;");
    };

    const getBalanceHTML = (amount, classNames) => {
        return "<span class='fw-bold fs-6" + (classNames ? " " + classNames.join(" ") : "") + "'>" + accounting.formatMoney(amount) + "</span>";
    };

    const formatPercentage = (percentage) => {
        return percentage ? percentage + "%" : "-";
    };

    const getHTML = (text, classNames) => {
        return text ? "<span class='fw-bold fs-5" + (classNames ? " " + classNames.join(" ") : "") + "'>" + text + "</span>" : "-";
    };

    const getRandomPassword = (length = 15) => {
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
        let password = "";
        for (let i = 0; i < length; i++) {
            const randomIndex = Math.floor(Math.random() * charset.length);
            password += charset[randomIndex];
        }
        return password;
    };

    const encrypt = (plaintext, secretKey) => {
        const ciphertext = CryptoJS.AES.encrypt(plaintext, CryptoJS.SHA256(secretKey), {
            mode: CryptoJS.mode.ECB,
            padding: CryptoJS.pad.Pkcs7,
        });
        return ciphertext.toString();
    };

    const decrypt = (ciphertext, secretKey) => {
        try {
            const bytes = CryptoJS.AES.decrypt(
                {
                    ciphertext: CryptoJS.enc.Base64.parse(ciphertext),
                },
                CryptoJS.SHA256(secretKey),
                {
                    mode: CryptoJS.mode.ECB,
                    padding: CryptoJS.pad.Pkcs7,
                }
            );
            return bytes.toString(CryptoJS.enc.Utf8);
        } catch (error) {
            return false;
        }
    };

    function shortenText(text, length = 50) {
        return text.slice(0, length) + (text.length > length ? "..." : "");
    }

    function shortenTxHash(txHash) {
        if (!txHash) {
            return "";
        }
        return txHash.slice(0, 6) + "..." + txHash.slice(-4);
    }

    function getPaymentMethodLogo(paymentMethod) {
        if (paymentMethod.method_name.replace(/\s+/g, "").includes("CashApp")) {
            return serverUrl + "/assets/web/media/cashapp.png";
        } else if (paymentMethod.method_name.includes("Zelle")) {
            return serverUrl + "/assets/web/media/zelle.png";
        } else if (paymentMethod.method_name.includes("PayPal")) {
            return serverUrl + "/assets/web/media/paypal.png";
        } else if (paymentMethod.method_name.includes("Chime")) {
            return serverUrl + "/assets/web/media/chime.png";
        } else if (paymentMethod.method_name.replace(/\s+/g, "").includes("ApplePay")) {
            return serverUrl + "/assets/web/media/applepay.png";
        } else if (paymentMethod.method_name.includes("Venmo")) {
            return serverUrl + "/assets/web/media/venmo.png";
        } else if (paymentMethod.method_name.includes("Skrill")) {
            return serverUrl + "/assets/web/media/skrill.png";
        } else {
            return null;
        }
    }

    function captureVendorCode(domain) {
        return domain.match(/^(.*?)\.tapsndr\.com$/)?.[1];
    }

    const badgeEmpty = "<span class='badge badge-light-warning'>N/A</span>";

    const mapLabelToRole = {
        master_admin: "Master Admin",
        admin: "Admin",
        fulfiller: "Fulfiller",
        user: "Client",
        distributor: "Distributor",
    };

    const mapBadgeToRole = {
        master_admin: '<span class="fw-bold text-danger">Master Admin</span>',
        admin: '<span class="fw-bold text-success">Admin</span>',
        distributor: '<span class="fw-bold text-pink">Distributor</span>',
        fulfiller: '<span class="fw-bold text-primary">Fulfiller</span>',
        user: '<span class="fw-bold text-info">Client</span>',
    };

    const emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
    const phoneRegex = /^\+?[1-9]{1,2}[0-9]{7,14}$/;

    return {
        isEmpty,
        ajax,
        getFormData,
        alert,
        toast,
        PN2,
        PN10,
        showLoading,
        hideLoading,
        getIDHTML,
        getUsernameHTML,
        getDateHTML,
        getBalanceHTML,
        formatPercentage,
        getHTML,
        getRandomPassword,
        encrypt,
        decrypt,
        shortenText,
        shortenTxHash,
        getPaymentMethodLogo,
        captureVendorCode,
        badgeEmpty,
        mapLabelToRole,
        mapBadgeToRole,
        emailRegex,
        phoneRegex,
    };
})();
