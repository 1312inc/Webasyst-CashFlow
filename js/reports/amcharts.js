
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
function chartCols (options) {

    const element = options.el;

    const chartContainer = document.createElement('div');
    chartContainer.classList.add('custom-p-16');
    element.appendChild(chartContainer);
    element.classList.add('width-33');

    // Create chart instance
    const chart = am4core.create(chartContainer, am4charts.XYChart);

    // Add data
    chart.data = options.data;

    // Create axes
    const xAxis = chart.xAxes.push(new am4charts.DateAxis());
    xAxis.renderer.grid.template.location = 1;
    xAxis.renderer.grid.template.stroke = chartColors.gray;
    xAxis.renderer.line.strokeOpacity = 0.2;
    xAxis.renderer.line.strokeWidth = 1;
    xAxis.renderer.line.stroke = chartColors.gray;
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
    series.columns.template.column.cornerRadiusTopLeft = 2
    series.columns.template.column.cornerRadiusTopRight = 2

    chart.cursor = new am4charts.XYCursor();
    
    chart.legend = new am4charts.Legend();
    chart.legend.itemContainers.template.paddingTop = 20
    chart.legend.useDefaultMarker = true;
    const marker = chart.legend.markers.template.children.getIndex(0);
    marker.cornerRadius(12, 12, 12, 12);

    const markerTemplate = chart.legend.markers.template;
    markerTemplate.width = 16;
    markerTemplate.height = 16;

}

/**
 * Render Totals Donut chart
 * @param {Object} options 
 * @param {Array} options.data
 * @param {HTMLElement} options.el
 * @param {String} options.currency – Currency sign
 */
function chartDonut (options) {

    const element = options.el;

    const chart = am4core.create(element, am4charts.PieChart);

    // Add data
    chart.data = options.data;

    // Add and configure Series
    const pieSeries = chart.series.push(new am4charts.PieSeries());
    pieSeries.dataFields.value = "value";
    pieSeries.dataFields.category = "name";
    pieSeries.slices.template.propertyFields.fill = "color";
    pieSeries.ticks.template.disabled = true;
    pieSeries.labels.template.disabled = true;      
    pieSeries.slices.template.tooltipText = "{category}: {value} " + options.currency;

    chart.legend = new am4charts.Legend();
    chart.legend.useDefaultMarker = true;
    const marker = chart.legend.markers.template.children.getIndex(0);
    marker.cornerRadius(12, 12, 12, 12);

    const markerTemplate = chart.legend.markers.template;
    markerTemplate.width = 16;
    markerTemplate.height = 16;

}

export {
    chartCols,
    chartDonut
};