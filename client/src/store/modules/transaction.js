import api from '@/plugins/api'
import { moment } from '@/plugins/numeralMoment'

export default {
  namespaced: true,

  state: () => ({
    transactions: [],
    updatedTransactions: [],
    createdTransactions: [],
    defaultGroupTransactions: [],
    activeGroupTransactions: [],
    groupNames: [],
    featurePeriod: 7,
    upcomingBlockOpened: 1, // TODO: Remove from the store to browser store
    chartData: [],
    chartDataCurrencyIndex: 0,
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
      return state.transactions.find(t => t.id === id)
    }
  },

  mutations: {
    setTransactions (state, data) {
      state.transactions = data
    },

    updateTransactions (state, data) {
      data.forEach(t => {
        const i = state.transactions.findIndex(e => e.id === t.id)
        if (i > -1) {
          state.transactions.splice(i, 1, t)
        }
        const i2 = state.createdTransactions.findIndex(e => e.id === t.id)
        if (i2 > -1) {
          state.createdTransactions.splice(i2, 1, t)
        }
      })
      state.transactions.sort((a, b) => {
        return new Date(b.date) - new Date(a.date)
      })
    },

    deleteTransaction (state, id) {
      const i = state.transactions.findIndex(e => e.id === id)
      if (i > -1) {
        state.transactions.splice(i, 1)
      }
    },

    setDefaultGroupTransactions (state, data) {
      state.defaultGroupTransactions = data
    },

    setActiveGroupTransactions (state, data) {
      state.activeGroupTransactions = data
    },

    setUpdatedTransactions (state, data) {
      state.updatedTransactions = data
      if (window.clearUpdatedTransactions) {
        clearTimeout(window.clearUpdatedTransactions)
      }
      window.clearUpdatedTransactions = setTimeout(() => {
        state.updatedTransactions = []
      }, 6000)
    },

    setCreatedTransactions (state, data) {
      data.forEach(t => {
        const i = state.createdTransactions.findIndex(e => e.id === t.id)
        if (i === -1) {
          state.createdTransactions.unshift(t)
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
      } catch (_) {
        return false
      }
    },

    async update ({ commit, dispatch }, params) {
      const method = params.id ? 'update' : 'create'
      try {
        const { data } = await api.post(`cash.transaction.${method}`, params)
        if (method === 'update') {
          commit('updateTransactions', data)
          commit('setUpdatedTransactions', data)
        }
        if (method === 'create') {
          commit('setCreatedTransactions', data)
        }
        dispatch('emitTransactionStateUpdate')
      } catch (_) {
        return false
      }
    },

    async delete ({ commit, dispatch }, id) {
      try {
        await api.delete('cash.transaction.delete', {
          params: { id }
        })
        commit('deleteTransaction', id)
        commit('deleteCreatedTransaction', [id])
        dispatch('emitTransactionStateUpdate')
      } catch (_) {
        return false
      }
    },

    async fetchTransactions ({ commit, state }) {
      try {
        const defaultParams = { ...state.queryParams }
        defaultParams.from = ''
        defaultParams.to = moment()
          .add(1, 'M')
          .format('YYYY-MM-DD')

        // if view details mode
        if (state.detailsInterval.from) {
          defaultParams.from = state.detailsInterval.from
        }
        if (state.detailsInterval.to) {
          defaultParams.to = state.detailsInterval.to
        }

        // setting offset
        const params = {
          ...defaultParams,
          offset: state.transactions.length
        }

        const { data } = await api.get('cash.transaction.getList', {
          params
        })

        const result =
          params.offset === 0
            ? [...data.data]
            : [...state.transactions, ...data.data]

        commit('setTransactions', result)
      } catch (_) {
        return false
      }
    },

    async fetchUpNextTransactions ({ commit }) {
      try {
        const { data } = await api.get('cash.transaction.getUpNextList', {
          params: {
            today: moment().format('YYYY-MM-DD')
          }
        })
        commit('setTransactions', data.data)
      } catch (_) {
        return false
      }
    },

    emitTransactionStateUpdate ({ dispatch }) {
      dispatch('account/getList', null, { root: true })
      dispatch('balanceFlow/getBalanceFlow', null, { root: true })
    }

  }
}
