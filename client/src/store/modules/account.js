import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    accounts: []
  }),
  mutations: {
    setAccounts (state, data) {
      state.accounts = data
    }
  },
  actions: {
    async getList ({ commit }, params) {
      const { data } = await api.get('cash.account.getList')
      commit('setAccounts', data)
    }
  }
}