import { AMCHARTS_LICENSE } from './constants.js';
import { createCurrencyToggler } from './currencyToggle.js';

export default function (chartdivSelector, data, language, allCurrenciesItemText) {

    const currencies = Object.keys(data);
    const mergedData = currencies.reduce((acc, c) => ([...acc, ...data[c]['data'].map(e => {
        return {
     ***REMOVED***e,
            currency: data[c].details.code,
            currencySign: data[c].details.sign
        };
    }
    )]), []).reverse();
    let activeCurrency = null;

    am4core.ready(() => {
        am4core.addLicense(AMCHARTS_LICENSE);
        am4core.useTheme(am4themes_animated);
        if (currencies.length > 1) {
            createCurrencyToggler('.currencies-container', currencies, (currency) => {
                activeCurrency = currency;
                renderSankey();
            }, allCurrenciesItemText);
        }
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
        chart.data = activeCurrency ? mergedData.filter(e => e.currency === data[activeCurrency].details.code) : mergedData;
        chart.dataFields.fromName = "from";
        chart.dataFields.toName = "to";
        chart.dataFields.value = "value";
        chart.dataFields.color = "color";
        chart.dataFields.currency = "currencySign";
        chart.links.template.tooltipText = `{fromName}→{toName}: {value} {currency}`;
        chart.links.template.colorMode = "gradient";
        chart.links.template.fillOpacity = 1;

        let hoverState = chart.links.template.states.create("hover");
        hoverState.properties.fillOpacity = 0.6;

    }

}