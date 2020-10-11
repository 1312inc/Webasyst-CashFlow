import Vue from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import Helpers from './plugins/helpers'
import VueI18n from 'vue-i18n'
import Numeral from './plugins/numeralMoment'
import Vuelidate from 'vuelidate'
import vClickOutside from 'v-click-outside'
import Noty from './plugins/noty'

import en from './locales/en_US.json'
import ru from './locales/ru_RU.json'

Vue.config.productionTip = false

Vue.use(VueI18n)
Vue.use(Numeral)
Vue.use(Noty)
Vue.use(Vuelidate)
Vue.use(Helpers)
Vue.use(vClickOutside)

const i18n = new VueI18n({
  locale: window?.appState?.lang || 'en_US',
  fallbackLocale: 'en_US',
  messages: {
    en_US: en,
    ru_RU: ru
  }
})

new Vue({
  router,
  store,
  i18n,
  render: h => h(App)
}).$mount('#app')
