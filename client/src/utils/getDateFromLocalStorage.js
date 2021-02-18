import moment from 'moment'

const setIntervalDate = (days, interval) => {
  return moment().add(days, interval).format('YYYY-MM-DD')
}

const intervals = {
  from: [
    {
      key: '-1_M',
      value: setIntervalDate(-1, 'M')
    },
    {
      key: '-3_M',
      value: setIntervalDate(-3, 'M')
    },
    {
      key: '-6_M',
      value: setIntervalDate(-6, 'M')
    },
    {
      key: '-1_Y',
      value: setIntervalDate(-1, 'Y')
    },
    {
      key: '-3_Y',
      value: setIntervalDate(-3, 'Y')
    },
    {
      key: '-5_Y',
      value: setIntervalDate(-5, 'Y')
    },
    {
      key: '-10_Y',
      value: setIntervalDate(-10, 'Y')
    }
  ],
  to: [
    {
      key: '0_M',
      value: setIntervalDate(0, 'd')
    },
    {
      key: '1_M',
      value: setIntervalDate(1, 'M')
    },
    {
      key: '3_M',
      value: setIntervalDate(3, 'M')
    },
    {
      key: '6_M',
      value: setIntervalDate(6, 'M')
    },
    {
      key: '1_Y',
      value: setIntervalDate(1, 'Y')
    },
    {
      key: '2_Y',
      value: setIntervalDate(2, 'Y')
    },
    {
      key: '3_Y',
      value: setIntervalDate(3, 'Y')
    }
  ]
}

export { intervals }

export default (type) => {
  const lsValue = localStorage.getItem(`interval_${type}`)
  return intervals[type].find((e) => e.key === lsValue)?.value
}
