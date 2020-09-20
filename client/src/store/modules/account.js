import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    accounts: []
  }),

  getters: {
    getAccountById: state => id => {
      return state.accounts.find(account => account.id === id)
    }
  },

  mutations: {
    setAccounts (state, data) {
      state.accounts = data
    }
  },

  actions: {
    async getList ({ commit }) {
      const { data } = await api.get('cash.account.getList')
      commit('setAccounts', data)
    },

    async update ({ commit }, params) {
      const method = params.id ? 'update' : 'create'
      const { data } = await api.post(`cash.account.${method}`, {
 ***REMOVED***params
      })
      console.log(data)
      // commit('setAccounts', data)
    }
  }
}
