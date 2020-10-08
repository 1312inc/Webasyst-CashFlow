import Vue from 'vue'
import App from './Mobile.vue'
import router from './router'
import store from './store'
import Helpers from './plugins/helpers'
import Numeral from './plugins/numeralMoment'
import Vuelidate from 'vuelidate'
import vClickOutside from 'v-click-outside'
import Noty from './plugins/noty'

Vue.config.productionTip = false

Vue.use(Numeral)
Vue.use(Noty)
Vue.use(Vuelidate)
Vue.use(Helpers)
Vue.use(vClickOutside)

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')