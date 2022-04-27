import { AMCHARTS_LICENSE } from './constants.js';
import { createCurrencyToggler } from './currencyToggle.js';

export default function (chartdivSelector, data, language) {

    const currencies = Object.keys(data.currencies);
    let activeCurrency = currencies[0];

    am4core.ready(() => {
        am4core.addLicense(AMCHARTS_LICENSE);
        am4core.useTheme(am4themes_animated);
        if (document.documentElement.dataset.theme === 'dark') {
            am4core.useTheme(am4themes_dark);
        }
        if (currencies.length > 1) {
            createCurrencyToggler('.currencies-container', currencies, (currency) => {
                activeCurrency = currency;
                renderStream();
            });
        }
        renderStream();
    });

    function renderStream () {

        am4core.disposeAllCharts();

        const chart = am4core.create(chartdivSelector, am4charts.XYChart);

        // Set Locale
        if (language !== 'en_US') {
            chart.language.locale = window[`am4lang_${language}`];
        }

        chart.data = data.data[activeCurrency];

        // Create axes
        const categoryAxis = chart.xAxes.push(new am4charts.DateAxis());
        categoryAxis.dataFields.category = "date";
        categoryAxis.renderer.grid.template.location = 0;
        categoryAxis.renderer.grid.template.stroke = '#888888';
        categoryAxis.renderer.minGridDistance = 50;
        categoryAxis.startLocation = 0.5;
        categoryAxis.endLocation = 0.5;

        const valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
        valueAxis.cursorTooltipEnabled = false;
        valueAxis.renderer.grid.template.disabled = true;
        valueAxis.renderer.labels.template.disabled = true;

        // Create series
        function createSeries (field, name, color) {
            const series = chart.series.push(new am4charts.LineSeries());
            series.dummyData = {
                field: field
            };
            series.dataFields.valueY = field + "_hi";
            series.dataFields.openValueY = field + "_low";
            series.dataFields.dateX = "date";
            series.name = name;
            series.stroke = am4core.color(color);
            series.fill = am4core.color(color);
            series.tooltipText = "{name}: {" + field + "} " + data.currencies[activeCurrency].sign;
            series.tooltip.background.filters.clear();
            series.tooltip.background.strokeWidth = 0;
            series.strokeWidth = 1;
            series.fillOpacity = 1;
            series.tensionX = 0.8;

            return series;
        }

        for (const id in data.categories) {
            createSeries(id, data.categories[id].name, data.categories[id].color);
        }

        // Cursor
        chart.cursor = new am4charts.XYCursor();
        chart.cursor.maxTooltipDistance = 0;
        chart.cursor.lineY.disabled = true;

        // Prepare data for the river-stacked series
        chart.events.on("beforedatavalidated", updateData);
        function updateData () {

            var data = chart.data;
            if (data.length == 0) {
                return;
            }

            for (var i = 0; i < data.length; i++) {
                var row = data[i];
                var sum = 0;

                // Calculate open and close values
                chart.series.each(function (series) {
                    var field = series.dummyData.field;
                    var val = Number(row[field]);
                    row[field + "_low"] = sum;
                    row[field + "_hi"] = sum + val;
                    sum += val;
                });

                // Adjust values so they are centered
                var offset = sum / 2;
                chart.series.each(function (series) {
                    var field = series.dummyData.field;
                    row[field + "_low"] -= offset;
                    row[field + "_hi"] -= offset;
                });

            }

        }

    }

}