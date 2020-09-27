export default {
  install (Vue) {
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
