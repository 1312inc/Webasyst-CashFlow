import Vue from 'vue'
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
import IsOnline from './plugins/isOnline'
import MobileEventBus from './plugins/mobileEventBus'
import './assets/styles/styles.css'

Vue.config.productionTip = false

Vue.use(permissions)
Vue.use(Numeral)
Vue.use(Vuelidate)
Vue.use(Errors)
Vue.use(Sticky)
Vue.use(Helpers)
Vue.use(IsOnline)

window.eventBus = MobileEventBus

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
