import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import Numeral from './plugins/numeralMoment'

Vue.config.productionTip = false

Vue.use(Numeral)

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
