import Vue from 'vue'
import { useDark } from '@vueuse/core'
import App from './Mobile.vue'
import router from './router'
import store from './store'
import permissions from './plugins/permissions'
import Helpers from './plugins/helpers'
import { i18n } from './plugins/locale'
import Numeral from './plugins/numeralMoment'
import Vuelidate from 'vuelidate'
import Errors from './plugins/errors'
import Sticky from './plugins/sticky'
import VuePortal from '@linusborg/vue-simple-portal'
import IsOnline from './plugins/isOnline'
import VueMeta from 'vue-meta'
import './assets/styles/styles.css'

Vue.config.productionTip = false
Vue.prototype.$isSpaMobileMode = true

Vue.use(permissions)
Vue.use(Numeral)
Vue.use(Errors)
Vue.use(Vuelidate)
Vue.use(Helpers)
Vue.use(Sticky)
Vue.use(VuePortal)
Vue.use(IsOnline)
Vue.use(VueMeta)

useDark({
  selector: 'html',
  attribute: 'data-theme',
  valueDark: 'dark',
  valueLight: 'light'
})

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
