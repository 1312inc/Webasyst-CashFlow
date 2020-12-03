import { locale } from './locale'
import numeral from 'numeral'
import moment from 'moment'
import 'numeral/locales/ru'

const localeCode = locale.split('_')[0]
numeral.locale(localeCode)
moment.locale(localeCode)

export { numeral }

export default {
  install (Vue, options) {
    Vue.prototype.$numeral = numeral
    Vue.prototype.$moment = moment
  }
}
