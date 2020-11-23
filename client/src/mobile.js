import Vue from 'vue'
import App from './Mobile.vue'
import router from './router'
import store from './store'
import permissions from './plugins/permissions'
import Helpers from './plugins/helpers'
import { i18n } from './plugins/locale'
import Numeral from './plugins/numeralMoment'
import Vuelidate from 'vuelidate'
import Noty from './plugins/noty'
import IsOnline from './plugins/isOnline'
import './assets/styles/tailwindcss.css'
import './assets/styles/wa-2.0.css'
import './assets/styles/transitions.css'

Vue.config.productionTip = false

Vue.use(permissions)
Vue.use(Numeral)
Vue.use(Noty)
Vue.use(Vuelidate)
Vue.use(Helpers)
Vue.use(IsOnline)

window.eventBus = new Vue()

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
