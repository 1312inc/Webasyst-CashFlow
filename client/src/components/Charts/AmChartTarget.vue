<template>
  <div id="chartTarget" />
</template>

<script setup>
import * as am4core from '@amcharts/amcharts4/core'
import * as am4charts from '@amcharts/amcharts4/charts'
// eslint-disable-next-line camelcase
import am4themes_animated from '@amcharts/amcharts4/themes/animated'
import { computed, onBeforeUnmount, onMounted, watch } from 'vue'

const props = defineProps({
  isPromoMode: {
    type: Boolean,
    default: false
  },
  isEmptyMode: {
    type: Boolean,
    default: false
  },
  amount: {
    type: Number,
    default: 50
  },
  amountFact: {
    type: Number,
    default: 50
  }
})

const maxAmount = computed(() => {
  return props.isPromoMode ? 146 : props.amount * 1.46
})

let chart
let intervalId

onMounted(() => {
  createChart()
})

onBeforeUnmount(() => {
  if (chart) {
    chart.dispose()
  }
})

watch(props, () => {
  if (chart) {
    chart.dispose()
    createChart()
  }
}, { deep: true })

function createChart () {
  if (intervalId) {
    clearInterval(intervalId)
  }

  am4core.useTheme(am4themes_animated)

  chart = am4core.create('chartTarget', am4charts.GaugeChart)
  chart.innerRadius = am4core.percent(82)

  /**
 * Axis for ranges
 */

  const axis2 = chart.xAxes.push(new am4charts.ValueAxis())
  axis2.min = 0
  axis2.max = maxAmount.value
  axis2.strictMinMax = true
  axis2.renderer.labels.template.disabled = true
  axis2.renderer.ticks.template.disabled = true
  axis2.renderer.grid.template.disabled = true

  if (props.isEmptyMode) {
    axis2.min = 0
    axis2.max = 100

    const range0 = axis2.axisRanges.create()
    range0.value = 0
    range0.endValue = 100
    range0.axisFill.fillOpacity = 1
    range0.axisFill.fill = '#EEEEEE'

    return
  }

  const range0 = axis2.axisRanges.create()
  range0.value = 0
  range0.endValue = props.amountFact
  range0.axisFill.fillOpacity = 1
  range0.axisFill.fill = props.isPromoMode ? '#22d13d' : '#22d13d'

  const range1 = axis2.axisRanges.create()
  range1.value = props.amountFact
  range1.endValue = maxAmount.value
  range1.axisFill.fillOpacity = 1
  range1.axisFill.fill = props.isPromoMode ? '#ed2509' : '#EEEEEE'

  /**
 * Label
 */

  const label = chart.radarContainer.createChild(am4core.Label)
  label.isMeasured = false
  label.fontSize = 35
  label.x = am4core.percent(50)
  label.y = am4core.percent(100)
  label.horizontalCenter = 'middle'
  label.verticalCenter = 'bottom'
  if (!props.isPromoMode) {
    label.text = props.amount > 0
      ? Math.round((props.amountFact / props.amount) * 100) + '%'
      : '0%'
  }

  /**
 * Hand
 */

  const hand = chart.hands.push(new am4charts.ClockHand())
  hand.axis = axis2
  hand.innerRadius = am4core.percent(20)
  hand.startWidth = 10
  hand.pin.disabled = true
  hand.value = props.amount

  if (props.isPromoMode) {
    hand.events.on('propertychanged', function (ev) {
      range0.endValue = ev.target.value
      range1.value = ev.target.value
      // label.text = axis2.positionToValue(hand.currentPosition).toFixed(1)
      axis2.invalidate()
    })

    intervalId = setInterval(function () {
      const value = Math.round(Math.random() * 50) + 50
      new am4core.Animation(hand, {
        property: 'value',
        to: value
      }, 1000, am4core.ease.cubicOut).start()
    }, 3000)
  }
}

</script>

<style>
#chartTarget {
    height: 200px;
}
</style>
