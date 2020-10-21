import api from '@/plugins/api'
import dumpDataByDay from '@/plugins/dumpDataByDay'

export default {
  namespaced: true,

  state: () => ({
    transactions: [],
    chartData: [],
    interval: {
      from: '',
      to: ''
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

    setInterval (state, interval) {
      state.interval = interval
    },

    setChartData (state, data) {
      state.chartData = data
    },

    setdetailsInterval (state, data) {
      state.detailsInterval = data
    },

    updateItem (state, data) {
      data.forEach(transaction => {
        const itemIndex = state.transactions.findIndex(e => e.id === transaction.id)
        if (itemIndex > -1) {
          state.transactions.splice(itemIndex, 1, transaction)
        } else {
          state.transactions.push(transaction)
          state.transactions.sort((a, b) => {
            if (a.date > b.date) {
              return -1
            }
            if (a.date < b.date) {
              return 1
            }
            return 0
          })
        }
      })
    },

    deleteItem (state, id) {
      const itemIndex = state.transactions.findIndex(e => e.id === id)
      if (itemIndex > -1) {
        state.transactions.splice(itemIndex, 1)
      }
    }
  },

  actions: {
    resetAllDataToInterval ({ state, dispatch, commit }, interval = {}) {
      const from = interval.from || state.interval.from
      const to = interval.to || state.interval.to

      commit('setInterval', { from, to })
      commit('setdetailsInterval', { from: '', to: '' })
      dispatch('getList', state.interval)
      dispatch('getChartData', state.interval)
    },

    setdetailsInterval ({ state, dispatch, commit }, interval = {}) {
      const from = interval.from || state.detailsInterval.from
      const to = interval.to || state.detailsInterval.to

      commit('setdetailsInterval', { from, to })
      dispatch('getList', state.detailsInterval)
    },

    async getList ({ commit }, interval) {
      try {
        const { data } = await api.get('cash.transaction.getList', {
          params: {
            from: interval.from,
            to: interval.to
          }
        })
        commit('setItems', data)
      } catch (e) {}
    },

    async getChartData ({ commit }, interval) {
      commit('setChartData', dumpDataByDay(interval.from, interval.to))
    },

    async update ({ commit }, params) {
      const method = params.id ? 'update' : 'create'

      const formData = new FormData()
      for (const key in params) {
        if (typeof params[key] === 'object' && params[key] !== null) {
          for (const p in params[key]) {
            formData.append(`${key}[${p}]`, params[key][p])
          }
        } else {
          formData.append(key, params[key])
        }
      }

      const { data } = await api.post(`cash.transaction.${method}`, formData)

      commit('updateItem', data)
    },

    async delete ({ commit }, id) {
      await api.delete('cash.transaction.delete', {
        params: { id }
      })

      commit('deleteItem', id)
    }
  }
}
