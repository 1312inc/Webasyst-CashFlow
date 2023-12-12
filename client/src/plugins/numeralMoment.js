import { locale } from './locale'
import numeral from 'numeral'
import moment from 'moment'
import 'numeral/locales/ru'
import 'moment/dist/locale/ru'

const localeCode = locale.split('_')[0] === 'ru' ? 'ru' : 'en'
numeral.locale(localeCode)
moment.locale(localeCode)

export { numeral, moment }

export default {
  install (Vue, options) {
    Vue.prototype.$numeral = numeral
    Vue.prototype.$moment = moment
  }
}
