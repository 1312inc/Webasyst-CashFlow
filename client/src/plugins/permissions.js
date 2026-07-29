import { appStateService } from '@/services/appState'

const permissions = appStateService.rights

export { permissions }

export default {
  install (Vue) {
    Vue.prototype.$permissions = permissions
  }
}
