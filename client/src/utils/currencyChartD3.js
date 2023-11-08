import * as d3 from 'd3'

class CurrencyChartD3 {
  margin = { top: 3, right: 0, bottom: 3, left: 0 }
  _uid = Math.random().toString(36).substring(2, 9)

  constructor (element, width, height, startDate, endDate) {
    this.element = element
    this.width = width
    this.height = height

    const daysInInterval = Math.round((endDate.getTime() - startDate.getTime()) / (1000 * 3600 * 24))
    this.timeline = new Array(daysInInterval)
      .fill(null)
      .map((e, i) => {
        return {
          period: new Date(startDate.getTime()).setDate(startDate.getDate() + i),
          amount: null
        }
      })
  }

  renderChart (chartData) {
    const data = this.timeline.map(item => ({ ...item }))

    // Merge empty period with days with data
    chartData.forEach(element => {
      const i = data.findIndex(e => new Date(e.period).toDateString() === new Date(element.period).toDateString())
      if (i > -1) {
        data.splice(i, 1, { ...element })
      }
    })

    // Filling daily amount
    let previosValue = 0
    data.map(e => {
      if (e.amount !== null) {
        previosValue = e.amount
      }
      if (e.amount === null) {
        e.amount = previosValue
      }
      return e
    })

    const svg = d3.select(this.element)
    svg.selectAll('*').remove()
    svg.attr('viewBox', [0, 0, this.width, this.height])

    const x = d3
      .scaleTime()
      .domain(d3.extent(data, d => new Date(d.period)))
      .range([this.margin.left, this.width - this.margin.right])

    const y = d3
      .scaleLinear()
      .domain([
        d3.min(data, d => d.amount),
        d3.max(data, d => d.amount)
      ])
      .nice()
      .range([this.height - this.margin.bottom, this.margin.top])
      .clamp(true)

    // Draw Balance Line Past
    const pastDates = data.filter(e => new Date().getTime() - new Date(e.period).getTime() >= 0)

    const linePast = d3
      .line()
      .x(d => x(new Date(d.period)))
      .y(d => y(d.amount))

    svg
      .append('g')
      .append('path')
      .attr(
        'style',
          `stroke: url(#line-gradient-${this._uid});fill: none;stroke-width: 2px;`
      )
      .attr('d', linePast(pastDates))

    // Draw Balance Line Future
    const futureDates = data.filter(e => new Date().getTime() - new Date(e.period).getTime() < 0)

    const lineFuture = d3
      .line()
      .x(d => x(new Date(d.period)))
      .y(d => y(d.amount))

    svg
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

    svg
      .append('path')
      .datum(data)
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

    svg
      .append('path')
      .datum(data)
      .attr('d', areaPos)
      .attr(
        'style',
          `fill: url(#area-gradient-positive-${this._uid});stroke-width: 0px;`
      )

    // Gradient positive area
    if (d3.max(data, d => d.amount) >= 0) {
      svg
        .append('linearGradient')
        .attr('id', `area-gradient-positive-${this._uid}`)
        .attr('gradientUnits', 'userSpaceOnUse')
        .attr('x1', 0)
        .attr('y1', y(0))
        .attr('x2', 0)
        .attr('y2', y(d3.max(data, d => d.amount)))
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
    if (d3.min(data, d => d.amount) < 0) {
      svg
        .append('linearGradient')
        .attr('id', `area-gradient-negative-${this._uid}`)
        .attr('gradientUnits', 'userSpaceOnUse')
        .attr('x1', 0)
        .attr('y1', y(0))
        .attr('x2', 0)
        .attr('y2', y(d3.min(data, d => d.amount)))
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
        Math.abs(d3.max(data, d => d.amount)) +
        Math.abs(d3.min(data, d => d.amount))
    const amountMax = d3.max(data, d => d.amount)
    const amountMin = d3.min(data, d => d.amount)
    let offset
    if (amountMax < 0) {
      offset = 0
    } else if (amountMin >= 0) {
      offset = this.height
    } else {
      offset =
          Math.ceil(
            (Math.abs(d3.max(data, d => d.amount)) / amountRange) * 100
          ) + '%'
    }

    svg
      .append('linearGradient')
      .attr('id', `line-gradient-${this._uid}`)
      .attr('gradientUnits', 'userSpaceOnUse')
      .attr('x1', 0)
      .attr('y1', 0)
      .attr('x2', 0)
      .attr('y2', this.height)
      .selectAll('stop')
      .data([
        { offset, color: '#3ec55e' },
        { offset, color: '#fc3d38' }
      ])
      .join('stop')
      .attr('offset', d => d.offset)
      .attr('stop-color', d => d.color)

    // current day pointer
    svg
      .append('circle')
      .attr('cx', x(new Date(futureDates[0].period)))
      .attr('cy', y(futureDates[0].amount))
      .attr('r', 3)
      .attr('fill', futureDates[0].amount < 0 ? '#fc3d38' : '#3ec55e')

    return this
  }
}

export { CurrencyChartD3 }
