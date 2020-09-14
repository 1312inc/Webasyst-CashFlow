
import moment from 'moment'

function randomInteger (min, max) {
  // получить случайное число от (min-0.5) до (max+0.5)
  const rand = min - 0.5 + Math.random() * (max - min + 1)
  return Math.round(rand)
}

const result = []

let i = 0
while (i < 1000) {
  result.push({ date: moment().add(-i, 'd').format('YYYY-MM-DD'), income: randomInteger(100000, 300000), expense: randomInteger(10000, 100000) })
  i++
}

export default result
