<template>
  <div
    class="c-bwc-container custom-mt-8"
    :style="`width: ${width}px; height: ${height}px`"
  >
    <svg ref="chart"></svg>
  </div>
</template>

<script>
import * as d3 from 'd3'
export default {
  props: {
    currency: {
      type: Object,
      requred: true
    }
  },

  data () {
    return {
      width: 200,
      height: 40
    }
  },

  computed: {
    data () {
      const istart = this.$moment().add(-1, 'M')
      const iend = this.$moment().add(3, 'M')
      const daysInInterval = iend.diff(istart, 'days') + 1

      // Filling empty data
      const filledChartData = new Array(daysInInterval)
        .fill(null)
        .map((e, i) => {
          return {
            period: this.$moment()
              .add(-1, 'M')
              .add(i, 'd')
              .format('YYYY-MM-DD'),
            amount: null
          }
        })

      // Merge empty period with days with data
      this.currency.data.forEach(element => {
        const i = filledChartData.findIndex(e => e.period === element.period)
        if (i > -1) {
          filledChartData.splice(i, 1, { ...element })
        }
      })

      // Filling daily amount
      let previosValue = 0
      filledChartData.map(e => {
        if (e.amount !== null) {
          previosValue = e.amount
        }
        if (e.amount === null) {
          e.amount = previosValue
        }
        return e
      })

      return filledChartData
    }
  },

  watch: {
    data () {
      this.renderChart()
    }
  },

  mounted () {
    this.renderChart()
  },

  methods: {
    renderChart () {
      const width = this.width
      const height = this.height
      const margin = { top: 3, right: 0, bottom: 3, left: 0 }

      this.svg = d3.select(this.$refs.chart)
      this.svg.selectAll('*').remove()
      this.svg.attr('viewBox', [0, 0, width, height])

      const x = d3
        .scaleTime()
        .domain(d3.extent(this.data, d => new Date(d.period)))
        .range([margin.left, width - margin.right])

      const y = d3
        .scaleLinear()
        .domain([
          d3.min(this.data, d => d.amount),
          d3.max(this.data, d => d.amount)
        ])
        .nice()
        .range([height - margin.bottom, margin.top])
        .clamp(true)

      // Draw Balance Line Past
      const pastDates = this.data.filter(e => {
        return this.$moment(e.period).isSameOrBefore(this.$helper.currentDate)
      })

      const linePast = d3
        .line()
        .x(d => x(new Date(d.period)))
        .y(d => y(d.amount))

      this.svg
        .append('g')
        .append('path')
        .attr(
          'style',
          `stroke: url(#line-gradient-${this._uid});fill: none;stroke-width: 2px;`
        )
        .attr('d', linePast(pastDates))

      // Draw Balance Line Future
      const futureDates = this.data.filter(e => {
        return this.$moment(e.period).isSameOrAfter(this.$helper.currentDate)
      })

      const lineFuture = d3
        .line()
        .x(d => x(new Date(d.period)))
        .y(d => y(d.amount))

      this.svg
        .append('g')
        .append('path')
        .attr(
          'style',
          `stroke: url(#line-gradient-${this._uid});fill: none;stroke-width: 2px;stroke-dasharray: 4,2;`
        )
        .attr('d', lineFuture(futureDates))

      // Draw Negative Area
      const areaNeg = d3
        .area()
        .x(d => x(new Date(d.period)))
        .y0(y(0))
        .y1(d => y(Math.min(0, d.amount)))

      this.svg
        .append('path')
        .datum(this.data)
        .attr('d', areaNeg)
        .attr(
          'style',
          `fill: url(#area-gradient-negative-${this._uid});stroke-width: 0px;`
        )

      // Draw Positive Area
      const areaPos = d3
        .area()
        .x(d => x(new Date(d.period)))
        .y0(y(0))
        .y1(d => y(Math.max(0, d.amount)))

      this.svg
        .append('path')
        .datum(this.data)
        .attr('d', areaPos)
        .attr(
          'style',
          `fill: url(#area-gradient-positive-${this._uid});stroke-width: 0px;`
        )

      // Gradient positive area
      if (d3.max(this.data, d => d.amount) >= 0) {
        this.svg
          .append('linearGradient')
          .attr('id', `area-gradient-positive-${this._uid}`)
          .attr('gradientUnits', 'userSpaceOnUse')
          .attr('x1', 0)
          .attr('y1', y(0))
          .attr('x2', 0)
          .attr('y2', y(d3.max(this.data, d => d.amount)))
          .selectAll('stop')
          .data([
            { offset: '0%', color: 'rgba(62, 197, 94, 0)' },
            { offset: '100%', color: 'rgba(62, 197, 94, 0.3)' }
          ])
          .enter()
          .append('stop')
          .attr('offset', d => d.offset)
          .attr('stop-color', d => d.color)
      }

      // Gradient negative area
      if (d3.min(this.data, d => d.amount) < 0) {
        this.svg
          .append('linearGradient')
          .attr('id', `area-gradient-negative-${this._uid}`)
          .attr('gradientUnits', 'userSpaceOnUse')
          .attr('x1', 0)
          .attr('y1', y(0))
          .attr('x2', 0)
          .attr('y2', y(d3.min(this.data, d => d.amount)))
          .selectAll('stop')
          .data([
            { offset: '0%', color: 'rgba(252, 61, 56, 0)' },
            { offset: '100%', color: 'rgba(252, 61, 56, 0.6)' }
          ])
          .enter()
          .append('stop')
          .attr('offset', d => d.offset)
          .attr('stop-color', d => d.color)
      }

      // Gradient for the line
      const amountRange =
        Math.abs(d3.max(this.data, d => d.amount)) +
        Math.abs(d3.min(this.data, d => d.amount))
      const amountMax = d3.max(this.data, d => d.amount)
      const amountMin = d3.min(this.data, d => d.amount)
      let offset
      if (amountMax < 0) {
        offset = 0
      } else if (amountMin >= 0) {
        offset = height
      } else {
        offset =
          Math.ceil(
            (Math.abs(d3.max(this.data, d => d.amount)) / amountRange) * 100
          ) + '%'
      }

      this.svg
        .append('linearGradient')
        .attr('id', `line-gradient-${this._uid}`)
        .attr('gradientUnits', 'userSpaceOnUse')
        .attr('x1', 0)
        .attr('y1', 0)
        .attr('x2', 0)
        .attr('y2', height)
        .selectAll('stop')
        .data([
          { offset: offset, color: '#3ec55e' },
          { offset: offset, color: '#fc3d38' }
        ])
        .join('stop')
        .attr('offset', d => d.offset)
        .attr('stop-color', d => d.color)

      // current day pointer
      this.svg
        .append('circle')
        .attr('cx', x(new Date(futureDates[0].period)))
        .attr('cy', y(futureDates[0].amount))
        .attr('r', 3)
        .attr('fill', futureDates[0].amount < 0 ? '#fc3d38' : '#3ec55e')
    }
  }
}
</script>

<style lang="scss">
.c-bwc-container {
  margin: 0 auto;

  svg {
    max-width: initial !important;
  }
}
</style>
