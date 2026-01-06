window.TapSNDRPage = (() => {
    const pageId = "tapsndr-user_activities";

    const selectors = {
        table: "." + pageId + "-table",
    };

    const ref = {
        userActivitiesTable: null,
        userActivities: null,
    };

    const getUserActivities = (searchParams, cb) => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/user_activities",
            {
                ...searchParams,
                orderField: "created_at",
                orderDirection: "desc",
                with: ["user"],
            },
            (success, data, error) => {
                if (!success) {
                    TapSNDRUtils.toast("error", error);
                    return;
                }
                cb(data);
            }
        );
    };

    const initDataTable = () => {
        const getTableData = (data) => {
            const tData = [];
            for (let i = 0; i < data.length; i++) {
                const rData = { ...data[i] };
                rData._id = data[i].id;
                rData.id = TapSNDRUtils.getIDHTML(data[i].id);
                rData.created_at = TapSNDRUtils.getDateHTML(data[i].created_at);
                rData.user = data[i].user ? data[i].user.username + " #" + data[i].user.id : "-";
                rData.description = data[i].description || "N/A";
                tData.push(rData);
            }
            return tData;
        };

        ref.userActivitiesTable = $(selectors.table).DataTable({
            processing: true,
            serverSide: true,
            ajax: ({ start, length, draw }, callback) => {
                getUserActivities(
                    {
                        pageIndex: start / length,
                        pageLength: length,
                    },
                    ({ total, data }) => {
                        ref.userActivities = data;
                        callback({
                            draw,
                            recordsTotal: total,
                            recordsFiltered: total,
                            data: getTableData(data),
                        });
                    }
                );
            },
            columns: [
                { title: "Created At", data: "created_at" },
                { title: "ID", data: "id" },
                { title: "User", data: "user" },
                { title: "Activity Type", data: "activity_type" },
                { title: "Description", data: "description" },
                { title: "IP Address", data: "ip_address" },
                { title: "Country", data: "country" },
            ],
            ordering: false,
            pageLength: 50,
            lengthMenu: [
                [50, 100, 200, 500],
                [50, 100, 200, 500],
            ],
        });
    };

    return {
        init: function () {
            initDataTable();
        },
    };
})();
