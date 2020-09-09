<template>
  <div class="home">
    <canvas ref="chart"></canvas>
  </div>
</template>

<script>
import Chart from 'chart.js'
import moment from 'moment'

export default {
  computed: {
    datasets () {
      const colors = ['rgba(255, 99, 22, 1)', 'rgba(22, 99, 132, 1)', 'rgba(255, 199, 132, 1)', 'rgba(255, 99, 232, 1)', 'rgba(253, 99, 132, 1)', 'rgba(55, 99, 132, 1)', '#3ad12e', '#000', '#000']
      let colorsCount = 0
      const res = this.$store.state.items.reduce((acc, e) => {
        if (!acc[`category_${e.category_id}`]) {
          acc[`category_${e.category_id}`] = {
            label: e.category_id,
            stack: +e.amount > 0 ? 'Stack Plus' : 'Stack Minus',
            data: [],
            backgroundColor: colors[colorsCount]
          }
          colorsCount++
        }
        acc[`category_${e.category_id}`].data.push({ x: e.date, y: +e.amount })
        return acc
      }, {})
      return Object.values(res)
    }
  },
  mounted () {
    moment.locale('ru')
    this.$store.dispatch('get', { from: '2020-01-01', to: '2020-09-08' }).then(() => {
      this.chart = new Chart(this.$refs.chart, {
        type: 'bar',
        data: {
          datasets: this.datasets
        },
        options: {
          tooltips: {
            mode: 'index',
            intersect: false
          },
          responsive: true,
          scales: {
            xAxes: [{
              stacked: true,
              type: 'time',
              time: {
                unit: 'month'
              }
            }],
            yAxes: [{
              stacked: true
            }]
          },
          layout: {
            padding: 60
          }
        }
      })
    })
  }
}
</script>
