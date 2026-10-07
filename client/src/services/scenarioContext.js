import { appStateService } from './appState'

const STORAGE_KEY = 'cashScenarioId'

export const scenarioContextService = {
  get scenarioId () {
    if (!appStateService.isPremium) return 0
    const scenarioId = localStorage.getItem(STORAGE_KEY)
    return isNaN(scenarioId) ? 0 : +scenarioId
  },

  set scenarioId (value) {
    if (!appStateService.isPremium) return
    if (!value) {
      localStorage.removeItem(STORAGE_KEY)
      return
    }
    localStorage.setItem(STORAGE_KEY, value.toString())
  }
}
