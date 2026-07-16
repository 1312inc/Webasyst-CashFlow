import api from '@/plugins/api'
import { appStateService } from '@/services/appState'

export default {
  namespaced: true,

  state: () => ({
    currencies: appStateService.currencies,
    companies: []
  }),

  getters: {
    getCurrencySignByCode: state => code => {
      return state.currencies.find(c => c.code === code)?.sign || ''
    }
  },

  mutations: {
    setCurrencies (state, data) {
      state.currencies = data
    },

    setCompanies (state, data) {
      state.companies = data
    }
  },

  actions: {
    async getCurrencies ({ commit, state }) {
      if (state.currencies.length < 1) {
        try {
          const { data } = await api.get('cash.system.getCurrencies')
          commit('setCurrencies', data)
        } catch (_) {
          return false
        }
      }
    },

    async getCompanies ({ commit, state }) {
      if (!appStateService.isPremium) {
        return false
      }
      try {
        const { data } = await api.get('cash.company.getList')
        if (Array.isArray(data)) {
          commit('setCompanies', data.sort((a, b) => a.sort - b.sort))
        }
      } catch (_) {
        return false
      }
    }
  }

}
