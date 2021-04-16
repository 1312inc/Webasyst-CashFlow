export default {
  install: Vue => {
    const IsOnline = new Vue({
      data () {
        return {
          online: true
        }
      },
      methods: {
        updateStatus () {
          if (typeof window.navigator.onLine === 'undefined') {
            this.online = true
          } else {
            this.online = window.navigator.onLine
          }
        }
      }
    })

    IsOnline.updateStatus()

    window.addEventListener('offline', () => {
      IsOnline.updateStatus()
    })
    window.addEventListener('online', () => {
      IsOnline.updateStatus()
    })

    Vue.prototype.$IsOnline = IsOnline
  }
}
