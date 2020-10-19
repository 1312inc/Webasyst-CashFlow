export default {
  install (Vue) {
    Vue.prototype.$isDesktopEnv = process.env.VUE_APP_MODE === 'desktop'

    Vue.prototype.$helper = {
      currToSymbol: (currency) => {
        if (currency === 'USD') {
          return '$'
        }
        if (currency === 'RUB') {
          return '₽'
        }
        return currency
      }
    }
  }
}
