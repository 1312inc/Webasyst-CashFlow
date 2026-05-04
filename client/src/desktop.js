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
import darkModeObserver from './plugins/darkModeObserver'
import VuePortal from '@linusborg/vue-simple-portal'
import WAtippy from './plugins/tippy'
import VueMeta from 'vue-meta'
import eventBus from './plugins/eventBus'
import { Plugin } from 'vue-fragment'
import { triggerTooltip } from './runtime/waTooltipWarmup'
import './assets/styles/styles.css'

import * as Sentry from '@sentry/vue'

Vue.config.productionTip = false
Vue.prototype.$isSpaMobileMode = false

Vue.use(permissions)
Vue.use(Numeral)
Vue.use(Errors)
Vue.use(Vuelidate)
Vue.use(Helpers)
Vue.use(darkModeObserver)
Vue.use(VuePortal)
Vue.use(WAtippy)
Vue.use(VueMeta)
Vue.use(eventBus)
Vue.use(Plugin)
triggerTooltip()
const contentSelector = '#app-content'
const sidebarSelector = '#app-sidebar'

if (document.querySelector(contentSelector)) {
  if (import.meta.env.MODE === 'development') {
    Sentry.init({
      Vue,
      dsn: 'https://83304f9c91de37513866d2f606b18021@o183199.ingest.us.sentry.io/4511269338546176',
      // Setting this option to true will send default PII data to Sentry.
      // For example, automatic IP address collection on events
      sendDefaultPii: true
    })
  }

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
