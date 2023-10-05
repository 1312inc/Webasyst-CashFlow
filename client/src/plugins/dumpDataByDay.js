import moment from 'moment'

function randomInteger (min, max, count = 0) {
  if (count > 20) {
    min = -min
    max = -max
  }
  if (count > 80) {
    min = -min
    max = -max
  }
  const rand = min - 0.5 + Math.random() * (max - min + 1)
  return Math.round(rand)
}

export default (start, end) => {
  const istart = moment(start)
  const iend = moment(end)
  const daysInInterval = iend.diff(istart, 'days') + 1

  const result = new Array(daysInInterval).fill(null).map((e, i) => {
    return {
      date: moment(start).add(i, 'd').format('YYYY-MM-DD'),
      income: randomInteger(100000, 300000),
      expense: randomInteger(10000, 100000),
      balance: randomInteger(10000, 100000, i / daysInInterval * 100)
    }
  })

  return result
}
