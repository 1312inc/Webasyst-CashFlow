const permissions = window.appState?.api_settings?.rights || {}

export { permissions }

export default {
  install (Vue) {
    Vue.prototype.$permissions = permissions
  }
}
