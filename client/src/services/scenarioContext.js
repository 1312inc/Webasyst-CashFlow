import { appStateService } from './appState'
import { deleteCookie, getCookie, setCookie } from '@/utils/cookie'

const COOKIE_KEY = 'cashScenarioId'

export const scenarioContextService = {
  get scenarioId () {
    if (!appStateService.isPremium) return 0
    const scenarioId = getCookie(COOKIE_KEY)
    return isNaN(scenarioId) ? 0 : +scenarioId
  },

  set scenarioId (value) {
    if (!appStateService.isPremium) return
    if (!value) {
      deleteCookie(COOKIE_KEY)
      return
    }
    setCookie(COOKIE_KEY, value.toString())
  }
}
