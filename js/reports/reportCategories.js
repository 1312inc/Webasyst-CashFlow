import { chartCols, chartDonut } from "./amcharts.js";

export default function (data) {

    // State
    const currencies = Object.entries({
 ***REMOVED***data.all_income, ...data.all_expense
    }).map(c => ({
        name: c[0],
        sign: c[1].helpers.currencySign
    }));
    let activeCurrencies = currencies[0];

    // Render Buttons
    for (const currency of currencies) {
        const button = document.createElement('span');
        button.innerText = currency.name;
        document.querySelector('.currencies-container').appendChild(button);
    }

    // Init waToggle
    if (window.jQuery) {
        $("#currencies-toggle span:first-child").addClass('selected');
        $("#currencies-toggle").show().waToggle({
            change: function (event, target, toggle) {
                activeCurrencies = currencies.find(c => c.name === $(target).text());
                renderCharts();
            }
        });
    }

    // Run Initial render
    am4core.ready(() => {
        am4core.addLicense('CH269543621');
        am4core.useTheme(am4themes_animated);
        renderCharts();
    });


    function renderCharts () {

        am4core.disposeAllCharts();

        function donutAdapter (data) {
            return Object.entries(data[activeCurrencies.name].data.colors).map((e, i) => {
                return {
                    name: e[0],
                    color: e[1],
                    value: Object.values(data[activeCurrencies.name].total)[i]
                };
            });
        }

        // Renders Donuts

        document.querySelectorAll('.c-charts').forEach(e => e.style.display = 'none');

        ;['income', 'expense'].forEach(type => {
            if (data[`all_${type}`][activeCurrencies.name]) {
                const el = document.querySelector(`#chartdiv_${type}`);
                el.parentElement.style.display = 'block';
                chartDonut({
                    el,
                    data: donutAdapter(data[`all_${type}`]),
                    currency: activeCurrencies.sign
                });
            }
        });

        // Render Columns Charts

        const container = document.querySelector('#cols');
        container.innerHTML = '';

        const colors = {
     ***REMOVED***data.all_income[activeCurrencies.name]?.data.colors || {},
     ***REMOVED***data.all_expense[activeCurrencies.name]?.data.colors || {}
        }

            ;[...data.all_income[activeCurrencies.name]?.data.columns || [],
     ***REMOVED***data.all_expense[activeCurrencies.name]?.data.columns || []].forEach(category => {

                const el = document.createElement('div');
                container.appendChild(el);

                const name = category[0];
                const cols = category.slice(1, -2).map((e, i) => {
                    return {
                        date: new Date(new Date().getFullYear(), i, 1),
                        value: e
                    };
                });

                chartCols({
                    el: el,
                    name: name,
                    color: colors[name][0],
                    data: cols,
                    currency: activeCurrencies.sign
                });
            });

    }

}