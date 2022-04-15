import { AMCHARTS_LICENSE } from './constants.js';
import { createCurrencyToggler } from './currencyToggle.js';

export default function (chartdivSelector, data, language) {

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

        // Set Locale
        if (language !== 'en_US') {
            chart.language.locale = window[`am4lang_${language}`];
        }
        
        chart.paddingTop = 30;
        chart.paddingRight = 90;
        chart.data = data[activeCurrency].data;
        chart.dataFields.fromName = "from";
        chart.dataFields.toName = "to";
        chart.dataFields.value = "value";
        chart.dataFields.color = "color";
        chart.links.template.tooltipText = `{fromName}→{toName}: {value} ${data[activeCurrency].details.sign}`;
        chart.links.template.colorMode = "gradient";
        chart.links.template.fillOpacity = 1;

        let hoverState = chart.links.template.states.create("hover");
        hoverState.properties.fillOpacity = 0.6;

    }

}