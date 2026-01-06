window.TapSNDRPage = (() => {
    const pageId = "tapsndr-dashboard-tickets";

    const selectors = {
        countByStatus: {
            chart: "." + pageId + "-chart-count_by_status",
        },
        dailyTotalAmount: (() => {
            const self = "." + pageId + "-daily_total_amount";
            return {
                self,
                chart: "." + pageId + "-daily_total_amount-chart",
                search: {
                    controls: {
                        up_to_current_time: self + ' input[name="up_to_current_time"]',
                        user_id: self + ' select[name="user_id"]',
                    },
                },
            };
        })(),
    };

    const prevStates = {
        users: null,
        countByStatus: null,
        dailyTotalAmount: null,
        searchParams: {
            dailyTotalAmount: null,
        },
    };
    const states = { ...prevStates };
    const ref = {
        countByStatusChart: null,
        dailyTotalAmountChart: null,
    };

    const getClients = (cb) => {
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
                cb(data);
            }
        );
    };

    const getStats = (cb) => {
        TapSNDRUtils.ajax("get", serverUrl + "/web/tickets/stats", (success, data, error) => {
            if (!success) {
                TapSNDRUtils.toast("error", error);
                return;
            }
            cb(data);
        });
    };

    const getDailyTotalAmount = (searchParams, cb) => {
        TapSNDRUtils.ajax(
            "get",
            serverUrl + "/web/tickets/stats/daily_total_amount",
            {
                ...searchParams,
                timezone: moment().format("Z"),
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

    const loadDailyTotalAmount = () => {
        getDailyTotalAmount(states.searchParams.dailyTotalAmount, (dailyTotalAmount) => {
            states.dailyTotalAmount = dailyTotalAmount;
            onStatesChanged();
        });
    };

    const initUsersSelect2 = () => {
        $(selectors.dailyTotalAmount.search.controls.user_id).html('<option value="">All Users</option>');
        for (let i = 0; i < states.users.length; i++) {
            const { id, username } = states.users[i];
            $(selectors.dailyTotalAmount.search.controls.user_id).append('<option value="' + id + '">' + username + " #" + id + "</option>");
        }
        $(selectors.dailyTotalAmount.search.controls.user_id).select2();
    };

    const initCountByStatusChart = () => {
        const element = document.querySelector(selectors.countByStatus.chart);

        const getElementWidth = () => {
            return parseInt(KTUtil.css(element, "width"));
        };

        const width = getElementWidth();

        if (!element) {
            return;
        }

        const options = {
            series: [],
            chart: {
                width,
                type: "pie",
            },
            labels: [],
            legend: {
                position: "bottom",
                formatter: function (seriesName, opts) {
                    return seriesName + " (" + opts.w.globals.series[opts.seriesIndex] + ")";
                },
            },
        };

        ref.countByStatusChart = new ApexCharts(element, options);
        ref.countByStatusChart.render();
    };

    const initDailyTotalAmountChart = () => {
        const element = document.querySelector(selectors.dailyTotalAmount.chart);

        const height = parseInt(KTUtil.css(element, "height"));

        if (!element) {
            return;
        }

        const options = {
            series: [],
            noData: {
                text: "Loading...",
            },
            chart: {
                height,
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: ["30%"],
                    endingShape: "rounded",
                },
            },
            legend: {
                show: false,
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                show: true,
                width: 2,
                colors: ["transparent"],
            },
            // title: {
            //     text: "Total amount of completed tickets per day",
            // },
            xaxis: {
                type: "datetime",
                axisBorder: {
                    show: false,
                },
                axisTicks: {
                    show: false,
                },
                labels: {
                    show: false,
                    style: {
                        fontSize: "12px",
                    },
                },
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: "12px",
                    },
                },
            },
            fill: {
                opacity: 1,
            },
            states: {
                normal: {
                    filter: {
                        type: "none",
                        value: 0,
                    },
                },
                hover: {
                    filter: {
                        type: "none",
                        value: 0,
                    },
                },
                active: {
                    allowMultipleDataPointsSelection: false,
                    filter: {
                        type: "none",
                        value: 0,
                    },
                },
            },
            tooltip: {
                style: {
                    fontSize: "12px",
                },
                y: {
                    formatter: function (val) {
                        return accounting.formatMoney(val);
                    },
                },
            },
            grid: {
                strokeDashArray: 4,
                yaxis: {
                    lines: {
                        show: true,
                    },
                },
            },
        };

        ref.dailyTotalAmountChart = new ApexCharts(element, options);
        ref.dailyTotalAmountChart.render();
    };

    const onStatesChanged = () => {
        if (states.users !== prevStates.users) {
            initUsersSelect2();
        }
        if (states.countByStatus !== prevStates.countByStatus) {
            const mapColorToStatus = {
                pending: "#feb019",
                sent: "#feb019",
                validated: "#775dd0",
                processing: "#008ffb",
                completed: "#00e396",
                declined: "#ff4560",
                reported: "#ff4560",
            };
            const statusInOrder = Object.keys(mapColorToStatus);
            ref.countByStatusChart.updateOptions({
                labels: statusInOrder.map((status) => _.upperFirst(status)),
                colors: statusInOrder.map((status) => mapColorToStatus[status]),
            });
            ref.countByStatusChart.updateSeries(statusInOrder.map((status) => states.countByStatus[status]));
        }

        if (states.dailyTotalAmount !== prevStates.dailyTotalAmount) {
            const dailyTotalAmount = [];
            if (states.dailyTotalAmount.length > 1) {
                const date = moment(states.dailyTotalAmount[0].date);
                const endDate = moment(_.last(states.dailyTotalAmount).date);
                while (date.isSameOrBefore(endDate, "day")) {
                    const formatted = date.format("YYYY-MM-DD");
                    dailyTotalAmount.push({
                        date: formatted,
                        total_amount: _.find(states.dailyTotalAmount, { date: formatted })?.total_amount || 0,
                    });
                    date.add(1, "day");
                }
            }
            ref.dailyTotalAmountChart.updateOptions({
                noData: {
                    text: "No data",
                },
                xaxis: {
                    labels: {
                        show: true,
                    },
                },
            });
            ref.dailyTotalAmountChart.updateSeries([
                {
                    name: "Total amount",
                    type: "line",
                    color: "#00A3FF",
                    data: dailyTotalAmount.map((item) => ({
                        x: item.date,
                        y: item.total_amount,
                    })),
                },
            ]);
        }

        if (states.searchParams.dailyTotalAmount !== prevStates.searchParams.dailyTotalAmount) {
            loadDailyTotalAmount();
        }

        prevStates.users = states.users;
        prevStates.countByStatus = states.countByStatus;
        prevStates.dailyTotalAmount = states.dailyTotalAmount;
        prevStates.searchParams.dailyTotalAmount = states.searchParams.dailyTotalAmount;
    };

    const setEvents = () => {
        for (const key in selectors.dailyTotalAmount.search.controls) {
            $(
                typeof selectors.dailyTotalAmount.search.controls[key] === "object"
                    ? selectors.dailyTotalAmount.search.controls[key].self
                    : selectors.dailyTotalAmount.search.controls[key]
            ).on("change", function () {
                states.searchParams = { ...states.searchParams };
                states.searchParams.dailyTotalAmount = { ...states.searchParams.dailyTotalAmount };
                if (key === "up_to_current_time") {
                    if ($(this).is(":checked")) {
                        states.searchParams.dailyTotalAmount.start_time = "00:00:00";
                        states.searchParams.dailyTotalAmount.end_time = moment().format("HH:mm:ss");
                    } else {
                        states.searchParams.dailyTotalAmount.start_time = null;
                        states.searchParams.dailyTotalAmount.end_time = null;
                    }
                } else {
                    states.searchParams.dailyTotalAmount[$(this).attr("name")] = $(this).val();
                }
                onStatesChanged();
            });
        }
    };

    return {
        init: function () {
            initCountByStatusChart();
            initDailyTotalAmountChart();
            setEvents();
            getClients((users) => {
                states.users = users;
                onStatesChanged();
            });
            getStats((stats) => {
                states.countByStatus = stats.count;
                onStatesChanged();
            });
            loadDailyTotalAmount();
        },
    };
})();
