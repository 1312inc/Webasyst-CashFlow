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
  window.cashFlowSetViewport = ({
    top, right, bottom, left, keyboard,
    'content-left': contentLeft = left,
    'content-right': contentRight = right,
    'sticky-top': stickyTop
  }) => {
    const insets = { top, right, bottom, left, keyboard, 'content-left': contentLeft, 'content-right': contentRight, 'sticky-top': stickyTop }
    for (const [edge, value] of Object.entries(insets)) {
      const property = `--cash-host-${edge}`
      if (Number.isFinite(value)) {
        root.style.setProperty(property, `${Math.max(0, value)}px`)
      } else {
        root.style.removeProperty(property)
      }
    }
    document.dispatchEvent(new Event('cashflowviewportchange'))
  }

  const dismissKeyboard = target => {
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
