import Vue from 'vue'
import AppStatePlugin, { appStateService } from '@/services/appState'
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
import VuePortal from '@linusborg/vue-simple-portal'
import IsOnline from './plugins/isOnline'
import VueMeta from 'vue-meta'
import { triggerTooltip } from './runtime/waTooltipWarmup'
import './assets/styles/styles.css'
import './assets/styles/native-viewport.css'

if (appStateService.nativeViewport) {
  const root = document.documentElement
  root.dataset.cashflowNativeViewport = ''
  window.cashFlowSetViewport = ({ top, right, bottom, left, keyboard, 'content-left': contentLeft = left, 'content-right': contentRight = right }) => {
    const insets = { top, right, bottom, left, keyboard, 'content-left': contentLeft, 'content-right': contentRight }
    for (const [edge, value] of Object.entries(insets)) {
      root.style.setProperty(`--cash-host-${edge}`, `${Math.max(0, value)}px`)
    }
    document.dispatchEvent(new Event('cashflowviewportchange'))
  }

  function dismissKeyboard (target) {
    const interactive = 'input, textarea, select, [contenteditable], button, a, label, [role="button"], [role="menuitem"], .wa-select, .dropdown, .popup'
    if (target instanceof Element && target.closest(interactive)) return
    const field = document.activeElement
    if (field?.matches('input, textarea, [contenteditable]')) field.blur()
  }

  document.addEventListener('click', event => dismissKeyboard(event.target))
}

Vue.config.productionTip = false
Vue.prototype.$isSpaMobileMode = true

Vue.use(AppStatePlugin)
Vue.use(permissions)
Vue.use(Numeral)
Vue.use(Errors)
Vue.use(Vuelidate)
Vue.use(Helpers)
Vue.use(VuePortal)
Vue.use(IsOnline)
Vue.use(VueMeta)
triggerTooltip()
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
