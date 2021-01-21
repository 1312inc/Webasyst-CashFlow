import store from '../store'
import { numeral } from '../plugins/numeralMoment'

export default {
  install (Vue) {
    Vue.prototype.$helper = {
      toCurrency: function (value, currencyCode) {
        return `${numeral(value).format('0,0[.]00')} ${this.currencySignByCode(
          currencyCode
        )}`
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
