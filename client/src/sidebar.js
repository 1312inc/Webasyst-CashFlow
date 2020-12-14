import Vue from 'vue'
import App from './Sidebar.vue'
import store from './store'
import SidebarRouterFallback from './plugins/sidebarRouterFallback'
import permissions from './plugins/permissions'
import Helpers from './plugins/helpers'
import { i18n } from './plugins/locale'
import Numeral from './plugins/numeralMoment'
import Vuelidate from 'vuelidate'
import './assets/styles/tailwindcss.css'
import './assets/styles/transitions.css'

Vue.config.productionTip = false

Vue.use(permissions)
Vue.use(Numeral)
Vue.use(Vuelidate)
Vue.use(Helpers)
Vue.use(SidebarRouterFallback)

new Vue({
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
