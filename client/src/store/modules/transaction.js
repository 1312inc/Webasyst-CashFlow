import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    incomingTransactions: [],
    upcomingTransactions: [],
    defaultGroupTransactions: null,
    activeGroupTransactions: [],
    groupNames: [],
    featurePeriod: 7,
    chartData: [],
    chartDataCurrencyIndex: 0,
    loading: true,
    loadingChart: true,
    queryParams: {
      from: '',
      to: '',
      limit: 30,
      offset: 0,
      filter: ''
    },
    detailsInterval: {
      from: '',
      to: ''
    }
  }),

  getters: {
    getTransactionById: state => id => {
      const allTransactions = [...state.upcomingTransactions, ...state.incomingTransactions]
      return allTransactions.find(t => t.id === id)
    }
  },

  mutations: {
    setIncomingTransactions (state, data) {
      state.incomingTransactions = data
    },

    setUpcomingTransactions (state, data) {
      state.upcomingTransactions = data
    },

    setDefaultGroupTransactions (state, data) {
      state.defaultGroupTransactions = data
    },

    setActiveGroupTransactions (state, data) {
      state.activeGroupTransactions = data
    },

    setGroupNames (state, data) {
      const i = state.groupNames.indexOf(data)
      if (i === -1) {
        state.groupNames.push(data)
      } else {
        state.groupNames.splice(i, 1)
      }
    },

    setFeaturePeriod (state, data) {
      state.featurePeriod = data
    },

    setChartData (state, data) {
      state.chartData = data
    },

    setDetailsInterval (state, data) {
      state.detailsInterval = data
    },

    setLoadingChart (state, data) {
      state.loadingChart = data
    },

    updateQueryParams (state, data) {
      const newData = { ...state.queryParams }
      for (const key in data) {
        if (key in newData) newData[key] = data[key]
      }
      state.queryParams = newData
    },

    setChartDataCurrencyIndex (state, value) {
      state.chartDataCurrencyIndex = value
    }

  },

  actions: {
    async getChartData ({ commit, state }) {
      const { from, to, filter } = state.queryParams
      commit('setLoadingChart', true)
      const { data } = await api.get('cash.aggregate.getChartData', {
        params: {
          from,
          to,
          filter,
          group_by: 'day'
        }
      })
      commit('setChartData', data)
      commit('setChartDataCurrencyIndex', 0)
      commit('setLoadingChart', false)
    },

    async update ({ dispatch }, params) {
      const method = params.id ? 'update' : 'create'
      await api.post(`cash.transaction.${method}`, params)
      dispatch('account/getList', null, { root: true })
    },

    async delete ({ dispatch }, id) {
      await api.delete('cash.transaction.delete', {
        params: { id }
      })
      dispatch('account/getList', null, { root: true })
    }

  }
}
