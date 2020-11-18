
export default {
  install (Vue) {
    Vue.prototype.$permissions = window.apiSettings?.rights || {}
  }
}
