import api from '@/plugins/api'
import dumpData from '@/plugins/dumpData'

export default {
  namespaced: true,

  state: () => ({
    listItems: [],
    fakeData: []
  }),
  mutations: {
    'SET_ITEMS' (state, data) {
      state.listItems = data
    },
    'SET_FAKE_ITEMS' (state, data) {
      state.fakeData = data
    }
  },
  actions: {
    async getList ({ commit }, params) {
      const { data } = await api.get('cash.transaction.getList', {
        params: {
          from: params.from,
          to: params.to
        }
      })
      commit('SET_ITEMS', data)
      commit('SET_FAKE_ITEMS', dumpData)
    }
  }
}
