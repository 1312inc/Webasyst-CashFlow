import numeral from 'numeral'
import moment from 'moment'
numeral.register('locale', 'ru', {
  delimiters: {
    thousands: ' ',
    decimal: ','
  },
  currency: {
    symbol: '₽'
  }
})
numeral.locale('ru')
moment.locale('ru')

export default {
  install (Vue, options) {
    Vue.prototype.$numeral = numeral
    Vue.prototype.$moment = moment
  }
}
