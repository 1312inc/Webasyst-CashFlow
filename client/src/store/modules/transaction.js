import api from '@/plugins/api'
import { moment } from '@/plugins/numeralMoment'
import getDateFromLocalStorage from '../../utils/getDateFromLocalStorage'

export default {
  namespaced: true,

  state: () => ({
    transactions: {
      data: [],
      limit: null,
      offset: null,
      total: null
    },
    queryParams: {
      from: '',
      to: moment().add(1, 'M').format('YYYY-MM-DD'),
      limit: 100,
      offset: 0,
      filter: ''
    },
    loading: false,
    chartInterval: {
      from: getDateFromLocalStorage('from') || moment().add(-1, 'Y').format('YYYY-MM-DD'),
      to: getDateFromLocalStorage('to') || moment().add(6, 'M').format('YYYY-MM-DD')
    },
    detailsInterval: {
      from: '',
      to: ''
    },
    updatedTransactions: [],
    createdTransactions: [],
    activeGroupTransactions: {
      index: null,
      name: '',
      items: []
    },
    featurePeriod: 7,
    upcomingBlockOpened: 1, // TODO: Remove from the store to browser store
    chartData: [],
    chartDataCurrencyIndex: 0,
    loadingChart: true,
    todayCount: {}
  }),

  getters: {
    getTransactionById: state => id => {
      return state.transactions.data.find(t => t.id === id)
    }
  },

  mutations: {
    setTransactions (state, data) {
      state.transactions = data
    },

    resetTransactions (state) {
      state.transactions = {
        data: [],
        limit: null,
        offset: null,
        total: null
      }
    },

    updateTransactions (state, data) {
      data.forEach(t => {
        const i = state.transactions.data.findIndex(e => e.id === t.id)
        if (i > -1) {
          state.transactions.data.splice(i, 1, t)
        }
        const i2 = state.createdTransactions.findIndex(e => e.id === t.id)
        if (i2 > -1) {
          state.createdTransactions.splice(i2, 1, t)
        }
      })
      state.transactions.data.sort((a, b) => {
        return new Date(b.date) - new Date(a.date)
      })
    },

    updateTransactionProps (state, { id, props }) {
      const i = state.transactions.data.findIndex(t => t.id === id)
      if (i > -1) {
        const newData = {
   ***REMOVED***state.transactions.data[i],
   ***REMOVED***props
        }
        state.transactions.data.splice(i, 1, newData)
      }
    },

    deleteTransaction (state, id) {
      const i = state.transactions.data.findIndex(e => e.id === id)
      if (i > -1) {
        state.transactions.data.splice(i, 1)
      }
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

    updateChartInterval (state, data) {
      state.chartInterval = {
 ***REMOVED***state.chartInterval,
 ***REMOVED***data
      }
    },

    setChartDataCurrencyIndex (state, value) {
      state.chartDataCurrencyIndex = value
    },

    setTodayCount (state, value) {
      state.todayCount = value
    }

  },

  actions: {
    async getChartData ({ commit, state }) {
      const { filter } = state.queryParams
      const from = state.chartInterval.from
      const to = state.chartInterval.to
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
        commit('setChartData', data === null ? [] : data)
        commit('setChartDataCurrencyIndex', 0)
      } catch (_) {
        return false
      } finally {
        commit('setLoadingChart', false)
      }
    },

    async update ({ commit, dispatch }, params) {
      const method = params.id ? 'update' : 'create'
      try {
        const { data } = await api.post(`cash.transaction.${method}`, params)
        if (!('silent' in params)) {
          if (method === 'update') {
            commit('updateTransactions', data)
            commit('setUpdatedTransactions', data)
          }
          if (method === 'create') {
            commit('setCreatedTransactions', data)
          }
        }
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
      } catch (_) {
        return false
      }
    },

    async fetchTransactions ({ commit, state }, userParams = {}) {
      try {
        commit('updateQueryParams', userParams)
        const params = { ...state.queryParams }

        if (params.offset === 0) {
          commit('setLoading', true)
        }
        // if view details mode
        if (state.detailsInterval.from) {
          params.from = state.detailsInterval.from
        }
        if (state.detailsInterval.to) {
          params.to = state.detailsInterval.to
        }

        const { data } = await api.get('cash.transaction.getList', {
          params
        })

        const result = {
   ***REMOVED***data,
   ***REMOVED***(data.offset > 0 && { data: [...state.transactions.data, ...data.data] })
        }

        commit('setTransactions', result)
      } catch (_) {
        return false
      } finally {
        commit('setLoading', false)
      }
    },

    async fetchUpNextTransactions ({ commit }) {
      try {
        commit('setLoading', true)
        const { data } = await api.get('cash.transaction.getUpNextList', {
          params: {
            today: moment().format('YYYY-MM-DD')
          }
        })
        commit('setTransactions', data)
      } catch (_) {
        return false
      } finally {
        commit('setLoading', false)
      }
    },

    updateDetailsInterval ({ commit, dispatch }, data) {
      commit('setDetailsInterval', data)
      dispatch('fetchTransactions', {
        offset: 0
      })
    },

    async getTodayCount ({ commit }) {
      try {
        const { data } = await api.get('cash.transaction.getTodayCount', {
          params: {
            today: moment().format('YYYY-MM-DD')
          }
        })
        commit('setTodayCount', data.count)
        // emitting for the mobile platform
        if (process.env.VUE_APP_MODE === 'mobile') {
          window.emitter.emit('todayCount', data.count)
        }
      } catch (_) {
        return false
      }
    }

  }
}
