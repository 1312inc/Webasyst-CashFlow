
const chartColors = {
    gray: am4core.color('#888888'),
};

/**
 * Render Categories Column chart
 * @param {Object} options 
 * @param {Array} options.data
 * @param {HTMLElement} options.el
 * @param {String} options.currency – Currency sign
 * @param {String} options.color
 * @param {string} options.name
 */
function chartCols (options, language) {

    const element = options.el;

    const chartContainer = document.createElement('div');
    chartContainer.classList.add('custom-p-16');
    element.appendChild(chartContainer);
    // element.classList.add('width-33');

    // Create chart instance
    const chart = am4core.create(chartContainer, am4charts.XYChart);

    // Set Locale
    if (language !== 'en_US') {
        chart.language.locale = window[`am4lang_${language}`];
    }

    // Add data
    chart.data = options.data;

    // Create axes
    const xAxis = chart.xAxes.push(new am4charts.DateAxis());
    xAxis.renderer.grid.template.location = 1;
    xAxis.renderer.grid.template.stroke = chartColors.gray;
    xAxis.renderer.labels.template.disabled = true;
    xAxis.renderer.cellStartLocation = 0.15;
    xAxis.renderer.cellEndLocation = 0.85;

    const yAxis = chart.yAxes.push(new am4charts.ValueAxis());
    yAxis.cursorTooltipEnabled = false;
    yAxis.renderer.grid.template.disabled = true;
    yAxis.renderer.labels.template.disabled = true;

    // Create series
    const series = chart.series.push(new am4charts.ColumnSeries());
    series.name = options.name;
    series.dataFields.valueY = "value";
    series.dataFields.dateX = "date";
    series.tooltipText = "{value} " + options.currency;
    series.stroke = am4core.color(options.color);
    series.fill = am4core.color(options.color);
    series.tooltip.background.filters.clear();
    series.tooltip.background.strokeWidth = 0;
    series.yAxis = yAxis;
    series.xAxis = xAxis;
    series.columns.template.strokeWidth = 0;
    series.columns.template.column.cornerRadiusTopLeft = 2;
    series.columns.template.column.cornerRadiusTopRight = 2;

    chart.cursor = new am4charts.XYCursor();
    chart.cursor.lineY.disabled = true;

    chart.legend = new am4charts.Legend();
    chart.legend.labels.template.text = `{name}: ${new Intl.NumberFormat(language.replace('_', '-')).format(chart.data.reduce((acc, e) => acc + e.value, 0))} ${options.currency}`;
    chart.legend.useDefaultMarker = true;
    const marker = chart.legend.markers.template.children.getIndex(0);
    marker.cornerRadius(12, 12, 12, 12);

    const markerTemplate = chart.legend.markers.template;
    markerTemplate.width = 16;
    markerTemplate.height = 16;

    // Future dates hover
    const rangeFututre = xAxis.axisRanges.create()
    rangeFututre.date = new Date()
    rangeFututre.endDate = new Date(8640000000000000)
    rangeFututre.grid.disabled = true
    rangeFututre.axisFill.fillOpacity = 0.5
    rangeFututre.axisFill.fill = '#ffffff'
    chart.seriesContainer.zIndex = -1

    // Currend day line
    const dateBorder = xAxis.axisRanges.create()
    dateBorder.date = new Date()
    dateBorder.grid.stroke = chartColors.gray
    dateBorder.grid.strokeWidth = 1
    dateBorder.grid.strokeOpacity = 0.6

}

/**
 * Render Totals Donut chart
 * @param {Object} options 
 * @param {Array} options.data
 * @param {HTMLElement} options.el
 * @param {String} options.currency – Currency sign
 */
function chartDonut (options, language) {

    const element = options.el;

    const chart = am4core.create(element, am4charts.PieChart);

    // Set Locale
    if (language !== 'en_US') {
        chart.language.locale = window[`am4lang_${language}`];
    }

    // Add data
    chart.data = options.data;

    // Add and configure Series
    const pieSeries = chart.series.push(new am4charts.PieSeries());
    pieSeries.radius = am4core.percent(70);
    pieSeries.dataFields.value = "value";
    pieSeries.dataFields.category = "name";
    pieSeries.slices.template.propertyFields.fill = "color";
    pieSeries.slices.template.propertyFields.isActive = "isProfit";
    pieSeries.ticks.template.adapter.add("disabled", (radius, target) => (target.dataItem && (target.dataItem.values.value.percent < 2)));
    pieSeries.labels.template.adapter.add("disabled", (radius, target) => (target.dataItem && (target.dataItem.values.value.percent < 2)));
    pieSeries.slices.template.tooltipText = "{category}: {value} " + options.currency;
}

export {
    chartCols,
    chartDonut
};