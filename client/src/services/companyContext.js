import { appStateService } from './appState'
import { deleteCookie, getCookie, setCookie } from '@/utils/cookie'

const COOKIE_KEY = 'cashCompanyId'

export const companyContextService = {
  get companyId () {
    if (!appStateService.isPremium) return 0
    const companyId = getCookie(COOKIE_KEY)
    return isNaN(companyId) ? 0 : +companyId
  },

  set companyId (value) {
    if (!appStateService.isPremium) return
    if (!value) {
      deleteCookie(COOKIE_KEY)
      return
    }
    setCookie(COOKIE_KEY, value.toString())
  }
}
