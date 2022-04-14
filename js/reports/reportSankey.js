import { AMCHARTS_LICENSE } from './constants.js';
import { createCurrencyToggler } from './currencyToggle.js';

export default function (chartdivSelector, data) {

    const currencies = Object.keys(data);
    let activeCurrency = currencies[0];

    am4core.ready(() => {
        am4core.addLicense(AMCHARTS_LICENSE);
        am4core.useTheme(am4themes_animated);
        createCurrencyToggler('.currencies-container', currencies, (currency) => {
            activeCurrency = currency;
            renderSankey();
        });
        renderSankey();
    });

    function renderSankey () {

        am4core.disposeAllCharts();

        const chart = am4core.create(chartdivSelector, am4charts.SankeyDiagram);
        chart.paddingTop = 30;
        chart.paddingRight = 90;
        chart.data = data[activeCurrency].data;
        chart.dataFields.fromName = "from";
        chart.dataFields.toName = "to";
        chart.dataFields.value = "value";
        chart.dataFields.color = "color";
        chart.links.template.tooltipText = `{fromName}→{toName}: {value} ${data[activeCurrency].details.sign}`;

    }

}