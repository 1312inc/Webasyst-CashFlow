import "./chartsD3Lib.umd.min.js";

const d = new Date();
const startDate = d.setMonth(d.getMonth() - 1);
const endDate = d.setMonth(d.getMonth() + 3);

let balanceflowWidgetDataFetchInProccess = false;

/**
 * Fetch balance flow api data
 * @param {Object} param0
 */
function fetchData ({ baseUrl, token }) {
    if (!balanceflowWidgetDataFetchInProccess) {
        balanceflowWidgetDataFetchInProccess = true;

        fetch(`${baseUrl}api.php/cash.aggregate.getBalanceFlow?access_token=${token}&from=${new Date(startDate).toISOString().slice(0, 10)}&to=${new Date(endDate).toISOString().slice(0, 10)}&group_by=day`)
            .then((response) => response.json())
            .then((result) => {
                balanceflowWidgetDataFetchInProccess = false;
                window.dispatchEvent(
                    new CustomEvent("balanceflowWidgetDataFetchFinished", {
                        detail: {
                            makeBallanceFlowWidget,
                            result
                        },
                    })
                );
            });
    }
};

/**
 * Generates widget
 * @param {Object} options
 * @param {string} options.container
 * @param {Object[]|Object} options.data - chart data or error object
 * @param {string} options.currencyCode
 * @param {string} options.currencySign
 * @param {string} options.size
 * @param {string} options.locale
 * @param {Object} options.localeMessages
 * @param {string} options.localeMessages.arrowUp
 * @param {string} options.localeMessages.arrowDown
 * @param {string} options.localeMessages.month
 * @param {string} options.localeMessages.noAccess
 * @returns
 */
function makeBallanceFlowWidget (options) {

    const alertMessage = (message) => {
        const $error = document.createElement("div");
        $error.innerHTML =
            `<div class="align-center gray" style="position: absolute; top: 50%;left: 50%; transform: translateX(-50%) translateY(-50%);">${message}</div>`;
        options.container.appendChild($error);
    };

    const spinner = options.container.querySelector('.cash-widget-spinner');
    if (spinner) {
        spinner.remove();
    }

    if (options.data.error) {
        alertMessage(options.data.error_description);
        return;
    }

    // if empty data
    if (!options.data.length) {
        alertMessage(options.localeMessages.noAccess);
        return;
    }

    const chartData = options.data.find(
        (d) => d.currency === options.currencyCode
    );

    if (!chartData) {
        return;
    }

    // Render chart element
    const chart = new chartsD3Lib.CurrencyChartD3(
        options.container.querySelector(".cash-widget-chart"),
        options.size === "1x1" ? 106 : 268,
        70,
        new Date(startDate),
        new Date(endDate)
    ).renderChart(chartData.data);

    // Create title
    const $containerBalance = options.container.querySelector(
        ".cash-widget-balance"
    );
    if (chartData.balances.now.amount < 0) {
        $containerBalance.classList.add("text-red");
    }
    $containerBalance.innerHTML =
        new Intl.NumberFormat(options.locale, {
            minimumFractionDigits: chartData.balances.now.amount % 1 === 0 ? 0 : 2,
        }).format(chartData.balances.now.amount) +
        " " +
        options.currencySign;

    // Average
    const $average = options.container.querySelector(".cash-widget-average");
    const averageAmount = (
        (chartData.balances.to.amount - chartData.balances.now.amount) /
        3
    ).toFixed(2);
    $average.classList.add(averageAmount >= 0 ? "text-green" : "text-red");
    $average.innerHTML =
        '<i class="fas fa-tachometer-alt"></i>' +
        " " +
        (averageAmount > 0 ? "+" : "") +
        new Intl.NumberFormat(options.locale).format(averageAmount) +
        " " +
        options.currencySign +
        "/" +
        options.localeMessages.month;

    // Make alert element
    const now = chartData.balances.now.date;
    const nowAmount = chartData.balances.now.amount;
    const alert = chartData.data.find((element) => {
        return (
            element.period > now &&
            nowAmount !== 0 &&
            Math.sign(nowAmount) !== Math.sign(element.amount)
        );
    });

    if (alert) {
        const $containerAlert =
            options.size === "1x1"
                ? options.container.querySelector(".cash-widget-average")
                : options.container.querySelector(".cash-widget-alert");
        $containerAlert.style.display = "block";
        $containerAlert.innerHTML = `
            <span title="${alert.amount >= 0
                ? options.localeMessages.arrowUp
                : options.localeMessages.arrowDown
            }" class="badge small squared nowrap ${alert.amount >= 0 ? "green" : "red"
            }">
              <i class="fas custom-mr-4 ${alert.amount >= 0
                ? "fa-arrow-circle-up"
                : "fa-exclamation-triangle"
            }" style="color: white;"></i> ${new Date(
                alert.period
            ).toLocaleDateString(options.locale, { month: "long", day: "numeric" })}
            </span>
            `;
    }
};

export { fetchData, makeBallanceFlowWidget };
