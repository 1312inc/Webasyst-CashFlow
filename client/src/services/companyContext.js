import appStateService from './appState'

export const companyContextService = {
  get companyId () {
    if (!appStateService.isPremium) return 0
    const companyId = localStorage.getItem('cashCompanyId')
    return isNaN(companyId) ? 0 : +companyId
  },

  set companyId (value) {
    if (!appStateService.isPremium) return
    localStorage.setItem('cashCompanyId', value.toString())
  }
}
