import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import Numeral from './plugins/numeralMoment'
import Vuelidate from 'vuelidate'
import Noty from './plugins/noty'

Vue.config.productionTip = false

Vue.use(Numeral)
Vue.use(Noty)
Vue.use(Vuelidate)

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
