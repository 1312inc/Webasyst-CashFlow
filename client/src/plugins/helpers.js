import store from '../store'
import { numeral } from '../plugins/numeralMoment'

export default {
  install (Vue) {
    Vue.prototype.$helper = {
      toCurrency: function (value, currencyCode, isDynamics = false, isReverse = false) {
        value = isReverse ? value * -1 : value
        const amount = numeral(isDynamics ? Math.abs(value) : value).format('0,0[.]00')
        const sign = isDynamics ? (value > 0 ? '+ ' : value < 0 ? '− ' : '') : ''
        const currency = this.currencySignByCode(currencyCode) ? ` ${this.currencySignByCode(currencyCode)}` : ''
        return `${sign}${amount}${currency}`
      },

      currencySignByCode: code => {
        return store.getters['system/getCurrencySignByCode'](code)
      },

      isDesktopEnv: process.env.VUE_APP_MODE === 'desktop',

      baseUrl: window?.appState?.baseUrl || '/',

      isValidHttpUrl: string => {
        let url

        try {
          url = new URL(string)
        } catch (_) {
          return false
        }

        return url.protocol === 'http:' || url.protocol === 'https:'
      },

      showMultiSelect () {
        return process.env.VUE_APP_MODE === 'mobile' ? window.eventBus.multiSelect : true
      }
    }
  }
}
