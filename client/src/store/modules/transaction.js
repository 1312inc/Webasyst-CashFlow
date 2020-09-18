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

  getters: {
    getDetailsDateInterval: state => {
      // return state.detailsDateIntervalUnit === 'day' ? state.detailsDate : 'month'
      return state.detailsDate
    }
  },

  mutations: {
    setItems (state, data) {
      state.listItems = data.reverse()
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
      const { data } = await api.get('cash.transaction.getList', {
        params: {
          from: params.from,
          to: params.to
        }
      })
      commit('setItems', data)
      commit('setFakeItems', dumpDataByDay('2018-08-15', '2020-08-15'))
    }
  }
}
