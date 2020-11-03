import RouterLinkFallback from '../components/RouterLinkFallback.vue'

export default {
  install (Vue) {
    Vue.component('router-link', RouterLinkFallback)
  }
}
