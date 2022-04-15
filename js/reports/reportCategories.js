import { AMCHARTS_LICENSE } from './constants.js';
import { createCurrencyToggler } from './currencyToggle.js';
import { chartCols, chartDonut } from "./amcharts.js";

export default function (data, language, year) {

    // State
    const currencySigns = Object.entries({
 ***REMOVED***data.all_income, ...data.all_expense
    }).reduce((acc, c) => {
        acc[c[0]] = c[1].helpers.currencySign;
        return acc;
    }, {});

    const currencies = Object.keys({
 ***REMOVED***data.all_income, ...data.all_expense
    });

    let activeCurrency = currencies[0];

    // Run Initial render
    am4core.ready(() => {
        am4core.addLicense(AMCHARTS_LICENSE);
        am4core.useTheme(am4themes_animated);
        if (currencies.length > 1) {
            createCurrencyToggler('.currencies-container', currencies, (currency) => {
                activeCurrency = currency;
                renderCharts();
            });
        }
        renderCharts();
    });

    function renderCharts () {

        am4core.disposeAllCharts();

        function donutAdapter (data) {
            return Object.entries(data[activeCurrency].total).map((e, i) => {
                const id = e[0];
                const name = data[activeCurrency].data.columns[id][0];
                const color = data[activeCurrency].data.colors[name][0];
                return {
                    name,
                    color,
                    value: e[1]
                };
            });
        }

        // Renders Donuts

        document.querySelectorAll('.c-charts').forEach(e => e.style.display = 'none');

        ;['income', 'expense'].forEach(type => {
            if (data[`all_${type}`][activeCurrency]) {
                const el = document.querySelector(`#chartdiv_${type}`);
                el.parentElement.style.display = 'block';
                chartDonut({
                    el,
                    data: donutAdapter(data[`all_${type}`]),
                    currency: currencySigns[activeCurrency]
                }, language);
            }
        });

        // Render Columns Charts

        const container = document.querySelector('#cols');
        container.innerHTML = '';

        ;['income', 'expense'].forEach(type => {
            if (data[`all_${type}`][activeCurrency]) {

                const currencyData = data[`all_${type}`][activeCurrency].data;
                const columns = currencyData.columns;

                for (const id in columns) {

                    const el = document.createElement('div');
                    container.appendChild(el);

                    const cols = columns[id].slice(1, -2).map((e, i) => {
                        return {
                            date: new Date(year, i, 1),
                            value: e
                        };
                    });

                    chartCols({
                        el: el,
                        name: columns[id][0],
                        color: currencyData.colors[columns[id][0]][0],
                        data: cols,
                        currency: currencySigns[activeCurrency]
                    }, language);

                }

            }
        });

    }

}