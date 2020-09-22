import api from '@/plugins/api'
import dumpDataByDay from '@/plugins/dumpDataByDay'

export default {
  namespaced: true,

  state: () => ({
    listItems: [],
    chartData: [],
    detailsDate: {
      from: null,
      to: null
    }
  }),

  mutations: {
    setItems (state, data) {
      state.listItems = data
    },
    setChartData (state, data) {
      state.chartData = data
    },
    setDetailsDate (state, data) {
      state.detailsDate = data
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
    },

    async getChartData ({ commit }) {
      commit('setChartData', dumpDataByDay('2018-08-15', '2021-02-15'))
    },

    async setDetailsDate ({ dispatch, commit }, dates) {
      dispatch('getList', dates)
      commit('setDetailsDate', dates)
    }
  }
}
