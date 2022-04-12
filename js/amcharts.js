
const chartColors = {
    gray: am4core.color('#888888'),
};

function chartCols (options) {

    const element = options.el;
    const data = options.data;

    const title = document.createElement('div');
    title.classList.add('align-center', 'bold');
    title.innerText = options.name;
    element.appendChild(title);

    const chartContainer = document.createElement('div');
    element.appendChild(chartContainer);

    // Create chart instance
    const chart = am4core.create(chartContainer, am4charts.XYChart);

    // Add data
    chart.data = data;

    // Create axes
    const xAxis = chart.xAxes.push(new am4charts.DateAxis());
    xAxis.renderer.grid.template.location = 1;
    xAxis.renderer.grid.template.stroke = chartColors.gray;
    xAxis.renderer.line.strokeOpacity = 0.2;
    xAxis.renderer.line.strokeWidth = 1;
    xAxis.renderer.line.stroke = chartColors.gray;
    xAxis.renderer.labels.template.fill = chartColors.gray;
    xAxis.renderer.labels.template.location = 0.5;
    xAxis.renderer.cellStartLocation = 0.15;
    xAxis.renderer.cellEndLocation = 0.85;

    const yAxis = chart.yAxes.push(new am4charts.ValueAxis());
    yAxis.cursorTooltipEnabled = false;
    yAxis.renderer.grid.template.disabled = true;
    yAxis.renderer.labels.template.disabled = true;

    // Create series
    const series = chart.series.push(new am4charts.ColumnSeries());
    series.dataFields.valueY = "value";
    series.dataFields.dateX = "date";
    series.tooltipText = "{value} " + options.currency;
    series.columns.template.fill = am4core.color(options.color);
    series.tooltip.background.filters.clear();
    series.tooltip.background.strokeWidth = 0;
    series.tooltip.getFillFromObject = false;
    series.tooltip.background.fill = am4core.color(options.color);
    series.yAxis = yAxis;
    series.xAxis = xAxis;
    series.columns.template.strokeWidth = 0;
    series.columns.template.fill = am4core.color(options.color);

    chart.cursor = new am4charts.XYCursor();

}

function chartDonut (options) {
    const element = document.querySelector(options.el);

    const chart = am4core.create(element, am4charts.PieChart);

    // Add data
    chart.data = options.data;

    // Add and configure Series
    const pieSeries = chart.series.push(new am4charts.PieSeries());
    pieSeries.dataFields.value = "value";
    pieSeries.dataFields.category = "name";
    pieSeries.slices.template.stroke = am4core.color("#fff");
    pieSeries.slices.template.strokeOpacity = 1;
    pieSeries.ticks.template.disabled = true;
    pieSeries.labels.template.disabled = true;      

    chart.legend = new am4charts.Legend();

}

export {
    chartCols,
    chartDonut
};