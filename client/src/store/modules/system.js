import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    currencies: window.appState?.currencies || []
  }),

  getters: {
    getCurrencySignByCode: state => code => {
      return state.currencies.find(c => c.code === code)?.sign || ''
    }
  },

  mutations: {
    setCurrencies (state, data) {
      state.currencies = data
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
    }
  }

}
