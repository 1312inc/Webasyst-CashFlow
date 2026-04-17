import { AMCHARTS_LICENSE } from './constants.js';
import { createCurrencyToggler } from './currencyToggle.js';

export default function (chartdivSelector, data, language, allCurrenciesItemText) {

    const currencies = Object.keys(data);
    const mergedData = [];

    // Merge data from all currencies, add currency info, and ensure a default color
    currencies.forEach(currencyKey => {
        const { data: currencyData, details } = data[currencyKey];
        
        currencyData.forEach(entry => {
         
            if(entry.direction === 'income') {
                
                const parentName = currencyData.find(e => e.from_id === entry.category_parent_id)?.from;
                mergedData.push({
                    ...entry,
                    ...(parentName ? { from: parentName, label: `${parentName}/${entry.from}` } : { label: entry.from }),
                    currency: details.code,
                    currencySign: details.sign,
                    color: entry.color || '#365fff'
                });

            } else {

                const parentName = currencyData.find(e => e.to_id === entry.category_parent_id)?.to;
                mergedData.push({
                    ...entry,
                    ...(parentName ? { to: parentName, label: `${parentName}/${entry.to}` } : { label: entry.to }),
                    currency: details.code,
                    currencySign: details.sign,
                    color: entry.color || '#365fff'
                });

            }
        })
    });


    // Reverse to match original behavior
    mergedData.reverse();
    let activeCurrency = null;

    am4core.ready(() => {
        am4core.addLicense(AMCHARTS_LICENSE);
        am4core.useTheme(am4themes_animated);
        if (document.documentElement.dataset.theme === 'dark') {
            am4core.useTheme(am4themes_dark);
        }
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
        chart.dataFields.label = "label";
        chart.links.template.tooltipText = `{fromName}→{label}: {value} {currency}`;
        chart.links.template.colorMode = "gradient";
        chart.links.template.fillOpacity = 0.6;

        const nodeTemplate = chart.nodes.template;
        nodeTemplate.adapter.add("visible", (e, target) => target.children.values[0].label.currentText !== 'stub');

        let hoverState = chart.links.template.states.create("hover");
        hoverState.properties.fillOpacity = 0.4;

    }

}