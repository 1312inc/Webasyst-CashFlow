import api from '@/plugins/api'
import { moment } from '@/plugins/numeralMoment'

export default {
  namespaced: true,

  state: () => ({
    transactions: {
      limit: 100,
      offset: 0,
      total: null,
      data: []
    },
    chartData: [],
    chartDataCurrencyIndex: 0,
    loading: true,
    loadingChart: true,
    queryParams: {
      from: '',
      to: '',
      limit: 100,
      offset: 0,
      filter: ''
    },
    detailsInterval: {
      from: '',
      to: ''
    },
    balanceFlow: []
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

    setBalanceFlow (state, data) {
      state.balanceFlow = data
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
    },

    setChartDataCurrencyIndex (state, value) {
      state.chartDataCurrencyIndex = value
    }

  },

  actions: {
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
        commit('setChartDataCurrencyIndex', 0)
        commit('setLoadingChart', false)
      } catch (e) {}
    },

    async getBalanceFlow ({ commit }) {
      const { data } = await api.get('cash.aggregate.getBalanceFlow', {
        params: {
          from: moment().add(-7, 'd').format('YYYY-MM-DD'),
          to: moment().add(1, 'M').format('YYYY-MM-DD'),
          group_by: 'day'
        }
      })
      commit('setBalanceFlow', data)
    },

    async update ({ dispatch }, params) {
      const method = params.id ? 'update' : 'create'
      await api.post(`cash.transaction.${method}`, params)
    },

    async delete ({ dispatch }, id) {
      await api.delete('cash.transaction.delete', {
        params: { id }
      })
    }

  }
}
