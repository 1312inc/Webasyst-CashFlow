import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    transactions: {
      limit: 30,
      offset: 0,
      total: null,
      data: []
    },
    chartData: [],
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
      return state.transactions.find(t => t.id === id)
    }
  },

  mutations: {
    setItems (state, data) {
      state.transactions = data
    },

    setChartData (state, data) {
      state.chartData = data
    },

    setDetailsInterval (state, data) {
      state.detailsInterval = data
    },

    setLoading (state, data) {
      state.loading = data
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
    }

  },

  actions: {
    async getList ({ commit, state }) {
      const params = { ...state.queryParams }
      if (state.detailsInterval.from) params.from = state.detailsInterval.from
      if (state.detailsInterval.to) params.to = state.detailsInterval.to
      commit('setLoading', true)
      try {
        const { data } = await api.get('cash.transaction.getList', {
          params
        })
        commit('setItems', data)
        commit('setLoading', false)
      } catch (e) {}
    },

    async getChartData ({ commit, state }) {
      const { from, to, filter } = state.queryParams
      commit('setLoadingChart', true)
      try {
        const { data } = await api.get('cash.aggregate.getChartData', {
          params: {
            from,
            to,
            filter,
            group_by: 'day'
          }
        })
        commit('setChartData', data)
        commit('setLoadingChart', false)
      } catch (e) {}
    },

    async update ({ dispatch }, params) {
      const method = params.id ? 'update' : 'create'
      await api.post(`cash.transaction.${method}`, params)
      dispatch('getList')
      dispatch('getChartData')
    },

    async delete ({ dispatch }, id) {
      await api.delete('cash.transaction.delete', {
        params: { id }
      })
      dispatch('getList')
      dispatch('getChartData')
    }
  }
}
