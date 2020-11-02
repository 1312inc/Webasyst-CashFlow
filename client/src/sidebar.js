import Vue from 'vue'
import App from './Sidebar.vue'
import store from './store'
import Helpers from './plugins/helpers'
import SidebarRouterFallback from './plugins/sidebarRouterFallback'
import { i18n } from './plugins/locale'
import Numeral from './plugins/numeralMoment'
import './assets/styles/tailwindcss.css'

Vue.config.productionTip = false

Vue.use(Numeral)
Vue.use(Helpers)
Vue.use(SidebarRouterFallback)

new Vue({
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
