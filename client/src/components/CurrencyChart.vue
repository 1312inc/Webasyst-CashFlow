<template>
  <div class="flexbox middle">
    <div
      class="c-bwc-container custom-mr-12"
      :style="`width: ${width}px; height: ${height}px`"
    >
      <svg ref="chart"></svg>
    </div>
    <div v-if="dataByCurrency" class="align-right">
      <div
        class="custom-mb-4 count"
        v-html="`${shorten}&nbsp;${$helper.currencySignByCode(this.currency)}`"
      ></div>
      <div>
        <span
          class="c-bwc-badge small"
          :class="diff >= 0 ? 'c-bwc-badge--green' : 'c-bwc-badge--red'"
          >{{ $numeral(diff).format() }}</span
        >
      </div>
    </div>
  </div>
</template>

<script>
import * as d3 from 'd3'
export default {
  props: {
    currency: {
      type: String,
      requred: true
    }
  },

  data () {
    return {
      width: 70,
      height: 30
    }
  },

  computed: {
    dataByCurrency () {
      return this.$store.state.transaction.balanceFlow.find(
        e => e.currency === this.currency
      )
    },

    data () {
      return this.dataByCurrency?.data
    },

    shorten () {
      return this.dataByCurrency?.balances.now.amountShorten
    },

    diff () {
      return (
        this.dataByCurrency?.balances.to.amount -
        this.dataByCurrency?.balances.from.amount
      )
    }
  },

  watch: {
    async data () {
      await this.$nextTick()
      this.renderChart()
    }
  },

  methods: {
    renderChart () {
      const width = this.width
      const height = this.height
      const margin = { top: 0, right: 0, bottom: 0, left: 0 }

      this.svg = d3.select(this.$refs.chart)
      this.svg.attr('viewBox', [0, 0, width, height])

      const x = d3
        .scaleUtc()
        .domain(d3.extent(this.data, d => this.$moment(d.period).toDate()))
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

      // Draw Balance Line
      const linePositive = d3
        .line()
        .x(d => x(this.$moment(d.period).toDate()))
        .y(d => y(d.amount))

      this.svg
        .append('g')
        .append('path')
        .attr(
          'style',
          `stroke: url(#line-gradient-${this._uid});fill: none;stroke-width: 2px;`
        )
        .attr('d', linePositive(this.data))

      // Draw Negative Area
      const areaNeg = d3
        .area()
        .x(d => x(this.$moment(d.period).toDate()))
        .y0(y(0))
        .y1(d => y(Math.min(1.0, d.amount)))

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
        .x(d => x(this.$moment(d.period).toDate()))
        .y0(y(0))
        .y1(d => y(Math.max(1.0, d.amount)))

      this.svg
        .append('path')
        .datum(this.data)
        .attr('d', areaPos)
        .attr(
          'style',
          `fill: url(#area-gradient-positive-${this._uid});stroke-width: 0px;`
        )

      // Gradient positive area
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
          { offset: '100%', color: 'rgba(62, 197, 94, 0.6)' }
        ])
        .enter()
        .append('stop')
        .attr('offset', d => d.offset)
        .attr('stop-color', d => d.color)

      // Gradient negative area
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

      // Gradient for the line
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
          { offset: y(0) / height, color: '#3ec55e' },
          { offset: y(0) / height, color: '#fc3d38' }
        ])
        .join('stop')
        .attr('offset', d => d.offset)
        .attr('stop-color', d => d.color)
    }
  }
}
</script>

<style lang="scss">
.c-bwc-badge {
  color: #fff;
  padding: 2px 6px;
  border-radius: 4px;

  &--green {
    background: #3ec55e;
  }

  &--red {
    background: #fc3d38;
  }
}

.c-bwc-container svg {
  max-width: 200px !important;
}
</style>
