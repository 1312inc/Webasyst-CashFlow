import api from '@/plugins/api'
import { moment } from '@/plugins/numeralMoment'
import getDateFromLocalStorage from '../../utils/getDateFromLocalStorage'
import { i18n } from '@/plugins/locale'
import { DEFAULT_FUTURE_PERIOD } from '@/utils/constants'

const mutationDelete = (state, ids) => {
  ids.forEach(id => {
    const i = state.transactions.data.findIndex(e => e.id === id)
    if (i > -1) {
      state.transactions.data.splice(i, 1)
      state.transactions.total = state.transactions.data.length
    }
  })
}

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
      limit: 50,
      offset: 0,
      filter: ''
    },
    loading: false,
    loadingFuture: false,
    chartInterval: {
      from: getDateFromLocalStorage('from') || moment().add(-1, 'Y').format('YYYY-MM-DD'),
      to: getDateFromLocalStorage('to') || moment().add(6, 'M').format('YYYY-MM-DD')
    },
    detailsInterval: {
      from: '',
      to: ''
    },
    activeGroupTransactions: {
      index: null,
      name: '',
      items: []
    },
    featurePeriod: 7,
    chartData: [],
    chartDataCurrencyIndex: 0,
    loadingChart: true,
    todayCount: {},
    showFutureTransactionsMoreLink: {
      7: false,
      30: false
    },
    isSplitFetchMode: false
  }),

  getters: {
    getFutureTransactions: state => {
      return state.transactions.data.filter(t => moment().isBefore(moment(t.date)))
    },
    getTransactionById: state => id => {
      return state.transactions.data.find(t => t.id === id)
    },
    getTransactionsWithoutJustCreated: state => {
      return state.transactions.data.filter(t => !t.$_flagCreated)
    },
    getTransactionsJustCreated: state => {
      return state.transactions.data.filter(t => t.$_flagCreated)
    },
    getChartDataByCurrentCurrency: state => {
      return state.chartData[state.chartDataCurrencyIndex]
    },
    activeCurrencyCode: state => {
      return state.chartData[state.chartDataCurrencyIndex]?.currency
    }
  },

  mutations: {
    setTransactions (state, data) {
      state.transactions = data
    },

    setShowFutureTransactionsMoreLink (state, data) {
      state.showFutureTransactionsMoreLink = data
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
      const updatedIDs = data.affected_transaction_ids
      updatedIDs.forEach((id, updatedIDIndex) => {
        const i = state.transactions.data.findIndex(e => e.id === id)
        if (i > -1) {
          const newData = {
            ...data,
            id: state.transactions.data[i].id,
            ...(updatedIDIndex > 0 && { create_datetime: state.transactions.data[i].create_datetime }),
            ...(updatedIDIndex > 0 && { date: state.transactions.data[i].date }),
            ...(updatedIDIndex > 0 && { datetime: state.transactions.data[i].datetime }),
            $_flagCreated: state.transactions.data[i].$_flagCreated,
            $_flagUpdated: true
          }
          state.transactions.data.splice(i, 1, newData)
        }
      })
      state.transactions.data.sort((a, b) => {
        return new Date(b.date) - new Date(a.date)
      })
    },

    updateTransactionProps (state, { ids, props }) {
      ids.forEach(id => {
        const i = state.transactions.data.findIndex(t => t.id === id)
        if (i > -1) {
          const newData = {
            ...state.transactions.data[i],
            ...props,
            $_flagUpdated: true
          }
          state.transactions.data.splice(i, 1, newData)
        }
      })
    },

    deleteTransaction: mutationDelete,

    deleteTransactionSilent: mutationDelete,

    setActiveGroupTransactions (state, data) {
      state.activeGroupTransactions = data
    },

    createTransactions (state, data) {
      if (Array.isArray(data)) {
        data.forEach(transition => {
          state.transactions.data.unshift({
            ...transition,
            $_flagCreated: true
          })
          state.transactions.total = state.transactions.data.length
        })
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

    setLoading (state, data) {
      state.loading = data
    },

    setLoadingFuture (state, data) {
      state.loadingFuture = data
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
        ...state.chartInterval,
        ...data
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
      let method = params.id ? 'update' : 'create'
      try {
        // if making transaction as repeating
        if (method === 'update' && params.is_repeating) {
          await dispatch('delete', { id: params.id, silent: true })
          method = 'create'
        }

        const { data } = await api.post(`cash.transaction.${method}`, params)

        if (!('silent' in params)) {
          if (method === 'update') {
            commit('updateTransactions', data)
          }
          if (method === 'create') {
            commit('createTransactions', data)
          }
        }
      } catch (e) {
        commit('errors/error', {
          title: 'error.api',
          method: '',
          message: e.response?.data?.error_description || i18n.t('error.html')
        }, { root: true })
        return Promise.reject(e)
      }
    },

    async delete ({ commit }, params) {
      const mutation = params.silent ? 'deleteTransactionSilent' : 'deleteTransaction'
      try {
        const { data: arrayOfIDs } = await api.post('cash.transaction.delete', params)
        commit(mutation, arrayOfIDs)
      } catch (_) {
        return false
      }
    },

    async fetchTransactionsFuture ({ commit, state, getters }, to) {
      commit('setLoadingFuture', true)
      try {
        const { data } = await api.get('cash.transaction.getList', {
          params: {
            ...state.queryParams,
            from: moment().add(1, 'day').format('YYYY-MM-DD'),
            to,
            offset: getters.getFutureTransactions.length,
            reverse: 1
          }
        })

        const isNotFullFutureOffset = data.data.length + data.offset < data.total
        commit('setShowFutureTransactionsMoreLink', {
          7: moment(data.data[data.data.length - 1].date).isBefore(moment().add(7, 'd')) && isNotFullFutureOffset,
          30: isNotFullFutureOffset
        })

        const result = {
          ...state.transactions,
          data: [...data.data.reverse(), ...state.transactions.data]
        }

        commit('setTransactions', result)
      } catch (_) {

      } finally {
        commit('setLoadingFuture', false)
      }
    },

    async fetchTransactions ({ commit, state, dispatch }, userParams = {}) {
      try {
        commit('updateQueryParams', userParams)
        const params = { ...state.queryParams }

        if (params.offset === 0) {
          commit('setTransactions', {
            ...state.transactions,
            data: []
          })
          commit('setLoading', true)

          if (!params.from && params.to && moment().isBefore(params.to)) {
            state.isSplitFetchMode = true
            dispatch('fetchTransactionsFuture', params.to)
            const today = moment().format('YYYY-MM-DD')
            commit('updateQueryParams', {
              to: today
            })
            params.to = today
          } else {
            state.isSplitFetchMode = false
          }
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
          ...data,
          data: [...state.transactions.data, ...data.data]
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
            today: moment().add(1, 'day').format('YYYY-MM-DD')
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
      if (!data.from && !data.to) {
        dispatch('fetchTransactions', {
          from: '',
          to: DEFAULT_FUTURE_PERIOD,
          offset: 0
        })
      } else {
        dispatch('fetchTransactions', {
          offset: 0
        })
      }
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
        if (this.$isSpaMobileMode) {
          window.emitter.emit('todayCount', data.count)
        }
      } catch (_) {
        return false
      }
    }

  }
}
