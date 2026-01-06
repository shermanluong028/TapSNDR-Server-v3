window.TapSNDRPage = (() => {
    const pageId = "tapsndr-settings-ticket-payment_methods";

    const selectors = {
        table: (() => {
            const self = "." + pageId + "-table";
            return {
                self,
                controls: {
                    active: self + ' input[type="checkbox"][name="active"]',
                },
            };
        })(),
    };

    // const states = {};
    const ref = {
        paymentMethodsTable: null,
        paymentMethods: null,
    };

    const getPaymentMethods = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/payment_methods", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const initDataTable = () => {
        const getTableData = (data) => {
            const tData = [];
            for (let i = 0; i < data.length; i++) {
                const rData = { ...data[i] };
                rData._id = data[i].id;
                rData.payment_method = '<img src="' + TapSNDRUtils.getPaymentMethodLogo(data[i]) + '" alt="" width="50px" />';

                rData.enabled = '<label class="form-check form-switch form-check-custom form-check-solid">';
                rData.enabled +=
                    '<input class="form-check-input" type="checkbox" name="active" value="' + data[i].id + '"' + (data[i].active ? " checked" : "") + " />";
                rData.enabled += "</label>";

                tData.push(rData);
            }
            return tData;
        };

        ref.paymentMethodsTable = $(selectors.table.self).DataTable({
            processing: true,
            serverSide: true,
            ajax: (_, callback) => {
                getPaymentMethods((data) => {
                    ref.paymentMethods = data;
                    callback({
                        recordsTotal: data.length,
                        recordsFiltered: data.length,
                        data: getTableData(data),
                    });
                });
            },
            columns: [
                { title: "Payment Method", data: "payment_method" },
                { title: "Enabled", data: "enabled" },
            ],
            ordering: false,
            paging: false,
            lengthChange: false,
            info: false,
            drawCallback: () => {
                setEvents();
            },
        });
    };

    const onActiveChanged = function () {
        const id = $(this).val();
        const active = Number($(this).prop("checked"));
        TapSNDRUtils.showLoading();
        TapSNDRUtils.ajax(
            "post",
            serverUrl + "/web/payment_methods",
            {
                id,
                active,
                _token: csrf_token,
            },
            (success, _, error) => {
                TapSNDRUtils.hideLoading();
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
            }
        );
    };

    const setEvents = () => {
        $(selectors.table.controls.active).on("change", onActiveChanged);
    };

    return {
        init: function () {
            setEvents();
            initDataTable();
        },
    };
})();
