import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    incomingTransactions: [],
    upcomingTransactions: [],
    updatedTransactionsIds: [],
    createdTransactions: [],
    defaultGroupTransactions: null,
    activeGroupTransactions: [],
    groupNames: [],
    featurePeriod: 7,
    upcomingBlockOpened: 1, // TODO: Remove from the store to browser store
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

    setUpdatedTransactionsIds (state, data) {
      state.updatedTransactionsIds = data
      setTimeout(() => {
        state.updatedTransactionsIds = []
      }, 6000)
    },

    setCreatedTransactions (state, data) {
      data.forEach(t => {
        const i = state.createdTransactions.findIndex(e => e.id === t.id)
        if (i === -1) {
          state.createdTransactions.push(t)
        } else {
          state.createdTransactions.splice(i, 1, t)
        }
      })
    },

    deleteCreatedTransaction (state, data) {
      if (!data.length) {
        state.createdTransactions = []
      }

      data.forEach(id => {
        const i = state.createdTransactions.findIndex(e => e.id === id)
        if (i > -1) {
          state.createdTransactions.splice(i, 1)
        }
      })
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

    setUpcomingBlockOpened (state, data) {
      state.upcomingBlockOpened = data
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
    },

    updateTransactions (state, data) {
      data.forEach(t => {
        const i = state.incomingTransactions.findIndex(e => e.id === t.id)
        if (i > -1) {
          state.incomingTransactions.splice(i, 1, t)
        } else {
          // state.incomingTransactions.unshift(t)
        }
        state.incomingTransactions.sort((a, b) => {
          return new Date(b.date) - new Date(a.date)
        })
      })
    },

    deleteTransaction (state, id) {
      const i = state.incomingTransactions.findIndex(e => e.id === id)
      if (i > -1) {
        state.incomingTransactions.splice(i, 1)
      }
    }

  },

  actions: {
    async getChartData ({ commit, state }) {
      const { from, to, filter } = state.queryParams
      try {
        commit('setLoadingChart', true)
        const { data } = await api.get('cash.aggregate.getChartData', {
          params: {
            from,
            to,
            filter,
            group_by: 'day'
          }
        })
        if (data.error) {
          throw new Error(data.error_message)
        }
        commit('setChartData', data)
        commit('setChartDataCurrencyIndex', 0)
        commit('setLoadingChart', false)
      } catch (e) {
        console.log(e)
      }
    },

    async update ({ commit, dispatch }, params) {
      const method = params.id ? 'update' : 'create'
      const { data } = await api.post(`cash.transaction.${method}`, params)
      commit('updateTransactions', data)
      commit('setCreatedTransactions', data)
      const ids = data.map(t => t.id)
      commit('setUpdatedTransactionsIds', ids)

      dispatch('account/getList', null, { root: true })
    },

    async delete ({ commit, dispatch }, id) {
      await api.delete('cash.transaction.delete', {
        params: { id }
      })
      commit('deleteTransaction', id)
      commit('deleteCreatedTransaction', [id])
      dispatch('account/getList', null, { root: true })
    }

  }
}
