
import moment from 'moment'

function randomInteger (min, max, count) {
  // получить случайное число от (min-0.5) до (max+0.5)
  if (count && count > 700) {
    min = -min
    max = -max
  }
  const rand = min - 0.5 + Math.random() * (max - min + 1)
  return Math.round(rand)
}

const result = []

let i = 0
while (i < 200) {
  result.push({
    date: moment().add(-i, 'd').format('YYYY-MM-DD'),
    income: randomInteger(100000, 300000),
    expense: randomInteger(10000, 100000),
    balance: randomInteger(10000, 100000, i)
  })
  i++
}

export default result
