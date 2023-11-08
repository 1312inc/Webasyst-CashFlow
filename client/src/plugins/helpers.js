import store from '../store'
import { numeral, moment } from '../plugins/numeralMoment'

export default {
  install (Vue) {
    Vue.prototype.$helper = {
      toCurrency: function (userOptions = {}) {
        const defaults = {
          value: null,
          currencyCode: null,
          isDynamics: false,
          isReverse: false,
          isAbs: false,
          prefix: ''
        }
        const options = { ...defaults, ...userOptions }
        const value = options.isReverse ? options.value * -1 : options.value
        const amount = numeral(options.isDynamics || options.isAbs ? Math.abs(value) : value).format('0,0[.]00')
        const sign = options.isDynamics ? (value > 0 ? '+ ' : value < 0 ? '− ' : '') : ''
        const currency = this.currencySignByCode(options.currencyCode) ? ` ${this.currencySignByCode(options.currencyCode)}` : ''
        const prefix = options.prefix || ''
        return `${prefix}${sign}${amount}${currency}`
      },

      currencySignByCode: code => {
        return store.getters['system/getCurrencySignByCode'](code)
      },

      currentDate: moment().format('YYYY-MM-DD'),

      isDesktopEnv: !window.appState.webView,

      baseUrl: window.appState.baseUrl || '/',

      accountName: window.appState.accountName || '',

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
        return store.state.multiSelectMode
      },

      isHeader () {
        return !!document.querySelector('#wa-header')
      },

      isTabletMediaQuery () {
        return window.matchMedia('(max-width: 1024px)').matches
      }
    }
  }
}
