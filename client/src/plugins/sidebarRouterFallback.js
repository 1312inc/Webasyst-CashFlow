
export default {
  install (Vue) {
    Vue.component('router-link', () => import('../components/RouterLinkFallback.vue'))
  }
}
