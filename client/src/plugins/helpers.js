export default {
  install (Vue) {
    Vue.prototype.$helper = {
      isDesktopEnv: process.env.VUE_APP_MODE === 'desktop',
      isValidHttpUrl: (string) => {
        let url

        try {
          url = new URL(string)
        } catch (_) {
          return false
        }

        return url.protocol === 'http:' || url.protocol === 'https:'
      }
    }
  }
}
