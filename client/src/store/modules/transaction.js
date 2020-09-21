import api from '@/plugins/api'
import dumpDataByDay from '@/plugins/dumpDataByDay'

export default {
  namespaced: true,

  state: () => ({
    listItems: [],
    fakeData: [],
    detailsDate: null,
    detailsDateIntervalUnit: null
  }),

  mutations: {
    setItems (state, data) {
      state.listItems = data
    },
    setFakeItems (state, data) {
      state.fakeData = data
    },
    setDetailsDate (state, { date, interval = null }) {
      state.detailsDate = date
      state.detailsDateIntervalUnit = interval
    }
  },

  actions: {
    async getList ({ commit }, params) {
      try {
        const { data } = await api.get('cash.transaction.getList', {
          params: {
            from: params.from,
            to: params.to
          }
        })
        commit('setItems', data)
      } catch (e) {}
      commit('setFakeItems', dumpDataByDay('2018-08-15', '2021-02-15'))
    }
  }
}
