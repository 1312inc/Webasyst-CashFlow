import store from '../store'
import { numeral } from '../plugins/numeralMoment'

export default {
  install (Vue) {
    Vue.prototype.$helper = {
      toCurrency: function (userOptions = {}) {
        const defaults = {
          value: null,
          currencyCode: null,
          isDynamics: false,
          isReverse: false,
          prefix: ''
        }
        const options = { ...defaults, ...userOptions }
        const value = options.isReverse ? options.value * -1 : options.value
        const amount = numeral(options.isDynamics ? Math.abs(value) : value).format('0,0[.]00')
        const sign = options.isDynamics ? (value > 0 ? '+ ' : value < 0 ? '− ' : '') : ''
        const currency = this.currencySignByCode(options.currencyCode) ? ` ${this.currencySignByCode(options.currencyCode)}` : ''
        const prefix = options.prefix || ''
        return `${prefix}${sign}${amount}${currency}`
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
