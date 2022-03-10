import './chartsD3Lib.umd.min.js';

/**
 * Generates widget
 * @param {Object} options
 * @param {string} options.container
 * @param {Object[]} options.chartData
 * @param {string} options.startDate
 * @param {string} options.endDate
 * @param {string} options.currencyCode
 * @param {string} options.currencySign
 * @param {string} options.size
 * @param {string} options.locale
 * @param {Object[]} options.alertMessage
 * @param {string} options.alertMessage.arrowUp
 * @param {string} options.alertMessage.arrowDown
 * @returns
 */
function makeBallanceFlowWidget (options) {

    const chartData = options.chartData.find((d) => d.currency === options.currencyCode);

    if (!chartData) {
        return;
    }

    // Render chart element
    const chart = new chartsD3Lib.CurrencyChartD3(
        options.container.querySelector('.cash-widget-chart'),
        options.size === "1x1" ? 106 : 268,
        70,
        new Date(options.startDate),
        new Date(options.endDate)
    ).renderChart(chartData.data);

    // Create title
    const $containerBalance = options.container.querySelector('.cash-widget-balance');
    if (chartData.balances.now.amount < 0) {
        $containerBalance.classList.add('text-red');
    }
    $containerBalance.innerHTML =
        new Intl.NumberFormat(options.locale).format(
            chartData.balances.now.amount
        ) +
        " " +
        options.currencySign;

    // Average
    const $average = options.container.querySelector(".cash-widget-average");
    const averageAmount = (
        (chartData.balances.to.amount - chartData.balances.now.amount) /
        3
    ).toFixed(2);
    $average.classList.add(
        averageAmount >= 0 ? "text-green" : "text-red"
    );
    $average.innerHTML =
        '<i class="fas fa-tachometer-alt"></i>' +
        " " +
        ( averageAmount > 0 ? '+' : '' ) +
        new Intl.NumberFormat(options.locale).format(averageAmount) +
        " " +
        options.currencySign +
        "/mo";

    // Make alert element
    const now = chartData.balances.now.date;
    const nowAmount = chartData.balances.now.amount;
    const alert = chartData.data.find(element => {
        return (
            element.period > now &&
            nowAmount !== 0 &&
            Math.sign(nowAmount) !== Math.sign(element.amount)
        );
    });

    if (alert) {
        const $containerAlert = options.size === '1x1' ? options.container.querySelector('.cash-widget-average') : options.container.querySelector('.cash-widget-alert');
        $containerAlert.style.display = 'block';
        $containerAlert.innerHTML = `
            <span title="${alert.amount >= 0 ? options.alertMessage.arrowUp : options.alertMessage.arrowDown}" class="badge small squared nowrap ${alert.amount >= 0 ? 'green' : 'red'}">
              <i class="fas custom-mr-4 ${alert.amount >= 0 ? 'fa-arrow-circle-up' : 'fa-exclamation-triangle'}" style="color: white;"></i> ${(new Date(alert.period)).toLocaleDateString(options.locale, { month: 'long', day: 'numeric' })}
            </span>
            `;
    }

}

export { makeBallanceFlowWidget };
