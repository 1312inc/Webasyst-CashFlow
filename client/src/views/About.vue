<template>
  <div class="about">
    <h1>This is an about page</h1>

    <div id="chartdiv"></div>
  </div>
</template>

<script>
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
// eslint-disable-next-line camelcase
// import am4themes_animated from '@amcharts/amcharts4/themes/animated'

export default {
  mounted () {
    /* Chart code */
    // Themes begin
    // am4core.useTheme(am4themes_animated)
    // Themes end

    // Create chart instance
    const chart = am4core.create('chartdiv', am4charts.XYChart)

    // Add data
    chart.data = [{
      year: '2003',
      europe: 2.5,
      namerica: 2.5,
      asia: 2.1,
      lamerica: 1.2,
      meast: 0.2,
      africa: 0.1
    }, {
      year: '2004',
      europe: 2.6,
      namerica: 2.7,
      asia: 2.2,
      lamerica: 1.3,
      meast: 0.3,
      africa: 0.1
    }, {
      year: '2005',
      europe: 2.8,
      namerica: 2.9,
      asia: 2.4,
      lamerica: 1.4,
      meast: 0.3,
      africa: 0.1
    }]

    // Create axes
    const categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis())
    categoryAxis.dataFields.category = 'year'
    categoryAxis.title.text = 'Local country offices'
    categoryAxis.renderer.grid.template.location = 0
    categoryAxis.renderer.minGridDistance = 20
    categoryAxis.renderer.cellStartLocation = 0.1
    categoryAxis.renderer.cellEndLocation = 0.9

    const valueAxis = chart.yAxes.push(new am4charts.ValueAxis())
    valueAxis.min = 0
    valueAxis.title.text = 'Expenditure (M)'

    // Create series
    function createSeries (field, name, stacked) {
      const series = chart.series.push(new am4charts.ColumnSeries())
      series.dataFields.valueY = field
      series.dataFields.categoryX = 'year'
      series.name = name
      series.columns.template.tooltipText = '{name}: [bold]{valueY}[/]'
      series.stacked = stacked
      series.columns.template.width = am4core.percent(95)
    }

    createSeries('europe', 'Europe', false)
    createSeries('namerica', 'North America', true)
    createSeries('asia', 'Asia', false)
    createSeries('lamerica', 'Latin America', true)
    createSeries('meast', 'Middle East', true)
    createSeries('africa', 'Africa', true)

    // Add legend
    chart.legend = new am4charts.Legend()
    this.chart = chart
  },

  beforeDestroy () {
    if (this.chart) {
      this.chart.dispose()
    }
  }
}
</script>

<style lang="scss" scoped>
  #chartdiv {
    height: 400px;
  }
</style>
