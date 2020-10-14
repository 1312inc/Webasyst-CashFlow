import Vue from 'vue'
import App from './Mobile.vue'
import router from './router'
import store from './store'
import Helpers from './plugins/helpers'
import { i18n } from './plugins/locale'
import Numeral from './plugins/numeralMoment'
import Vuelidate from 'vuelidate'
import Noty from './plugins/noty'

Vue.config.productionTip = false

Vue.use(Numeral)
Vue.use(Noty)
Vue.use(Vuelidate)
Vue.use(Helpers)

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
