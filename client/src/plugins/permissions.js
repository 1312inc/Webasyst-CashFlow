
export default {
  install (Vue) {
    Vue.prototype.$permissions = window.appState?.api_settings?.rights || {}
  }
}
