import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import permissions from './plugins/permissions'
import Helpers from './plugins/helpers'
import { i18n } from './plugins/locale'
import Numeral from './plugins/numeralMoment'
import Vuelidate from 'vuelidate'
import Notify from './plugins/notify'
import './assets/styles/tailwindcss.css'
import './assets/styles/transitions.css'

Vue.config.productionTip = false

Vue.use(permissions)
Vue.use(Numeral)
Vue.use(Notify)
Vue.use(Vuelidate)
Vue.use(Helpers)

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
