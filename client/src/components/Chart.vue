<template>
  <canvas ref="chart"></canvas>
</template>

<script>
import Chart from 'chart.js'

import { mapState } from 'vuex'

const colors = [
  'rgba(255, 99, 22, 0.5)',
  'rgba(22, 99, 132, 0.5)',
  'rgba(255, 199, 132, 0.5)',
  'rgba(255, 99, 232, 0.5)',
  'rgba(253, 99, 132, 0.5)',
  'rgba(55, 99, 132, 0.5)',
  'rgba(55, 129, 132, 0.5)',
  'rgba(205, 199, 132, 0.5)',
  'rgba(155, 99, 132, 0.5)',
  'rgba(125, 119, 12, 0.5)'
]

export default {
  name: 'Chart',
  watch: {
    listItems () {
      this.renderChart()
    }
  },
  computed: {
    ...mapState(['listItems']),

    datasets () {
      const datesInList = this.listItems.reduce((acc, listItem) => {
        if (!acc.includes(listItem.date)) acc.push(listItem.date)
        return acc
      }, [])

      const datasets = this.listItems.reduce((datasetsObject, listItem, i, reduceArray) => {
        if (!datasetsObject[listItem.category_id]) {
          const datasetData = []
          datesInList.forEach((date) => {
            const summa = reduceArray.filter(
              (el) => el.category_id === listItem.category_id && el.date === date
            ).reduce((summa, e) => {
              return summa + +e.amount
            }, 0)
            datasetData.push({ x: date, y: Math.abs(summa) })
          })

          datasetsObject[listItem.category_id] = {
            type: 'bar',
            label: listItem.category_id,
            stack: +listItem.amount > 0 ? 'Plus' : 'Minus',
            data: datasetData,
            backgroundColor: colors[Object.keys(datasetsObject).length],
            borderWidth: 1
          }
        }
        return datasetsObject
      }, {})

      return Object.values(datasets).sort((a, b) => {
        if (a.stack > b.stack) {
          return -1
        }
        if (a.stack < b.stack) {
          return 1
        }
        return 0
      })
    }
  },

  methods: {
    renderChart () {
      if (this.chart) {
        this.chart.destroy()
      }

      this.chart = new Chart(this.$refs.chart, {
        data: {
          datasets: this.datasets
        },
        options: {
          tooltips: {
            xPadding: 12,
            yPadding: 12,
            cornerRadius: 3,
            callbacks: {
              title: (tooltipItem) => {
                return this.$moment(tooltipItem[0].label).format('ll')
              },
              label: (tooltipItem, data) => {
                const label = data.datasets[tooltipItem.datasetIndex].label || ''
                return `${label}: ${this.$numeral(tooltipItem.yLabel).format('0,0 $')}`
              }
            }
          },
          scales: {
            xAxes: [
              {
                stacked: true,
                distribution: 'series',
                offset: true,
                type: 'time',
                time: {
                  unit: 'month'
                },
                gridLines: {
                  display: false
                }
              }
            ],
            yAxes: [
              {
                stacked: true,
                ticks: {
                  beginAtZero: true,
                  callback: (value, index, values) => {
                    return this.$numeral(value).format('0,0 $')
                  }
                }
              }
            ]
          },
          layout: {
            padding: 60
          }
        }
      })
    }
  }
}
</script>
