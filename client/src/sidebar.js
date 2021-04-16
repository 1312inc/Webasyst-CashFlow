import Vue from 'vue'
import App from './Sidebar.vue'
import router from './router'
import SidebarRouterFallback from './plugins/sidebarRouterFallback'
import store from './store'
import permissions from './plugins/permissions'
import Helpers from './plugins/helpers'
import { i18n } from './plugins/locale'
import Numeral from './plugins/numeralMoment'
import Vuelidate from 'vuelidate'
import Errors from './plugins/errors'
import darkModeObserver from './plugins/darkModeObserver'
import VuePortal from '@linusborg/vue-simple-portal'
import './assets/styles/styles.css'

Vue.config.productionTip = false

Vue.use(permissions)
Vue.use(Numeral)
Vue.use(Vuelidate)
Vue.use(Helpers)
Vue.use(SidebarRouterFallback)
Vue.use(Errors)
Vue.use(darkModeObserver)
Vue.use(VuePortal)

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
