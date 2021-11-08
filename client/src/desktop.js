import Vue from 'vue'
import App from './App.vue'
import Sidebar from './components/Sidebar/Sidebar'
import router from './router'
import store from './store'
import permissions from './plugins/permissions'
import SidebarRouterFallback from './plugins/sidebarRouterFallback'
import Helpers from './plugins/helpers'
import { i18n } from './plugins/locale'
import Numeral from './plugins/numeralMoment'
import Vuelidate from 'vuelidate'
import Errors from './plugins/errors'
import Sticky from './plugins/sticky'
import darkModeObserver from './plugins/darkModeObserver'
import VuePortal from '@linusborg/vue-simple-portal'
import WAtippy from './plugins/tippy'
import './assets/styles/styles.css'

Vue.config.productionTip = false

Vue.use(permissions)
Vue.use(Numeral)
Vue.use(Errors)
Vue.use(Vuelidate)
Vue.use(Helpers)
Vue.use(Sticky)
Vue.use(darkModeObserver)
Vue.use(VuePortal)
Vue.use(WAtippy)

const contentSelector = '#app-content'
const sidebarSelector = '#app-sidebar'

if (document.querySelector(contentSelector)) {
  new Vue({
    router,
    store,
    i18n,
    render: h => h(App)
  }).$mount(contentSelector)
} else {
  Vue.use(SidebarRouterFallback)
}

if (document.querySelector(sidebarSelector)) {
  new Vue({
    router,
    store,
    i18n,
    render: h => h(Sidebar)
  }).$mount(sidebarSelector)
}
