import RouterLinkFallback from '../components/RouterLinkFallback.vue'

export default {
  install (Vue) {
    Vue.component('RouterLink', RouterLinkFallback)
  }
}
