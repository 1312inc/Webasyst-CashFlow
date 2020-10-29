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

    setInterval (state, interval) {
      state.interval = interval
    },

    setChartData (state, data) {
      state.chartData = data
    },

    setdetailsInterval (state, data) {
      state.detailsInterval = data
    },

    setLoading (state, data) {
      state.loading = data
    },

    updateQueryParams (state, data) {
      const newData = { ...state.queryParams }
      for (const key in data) {
        if (key in newData) newData[key] = data[key]
      }
      state.queryParams = newData
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
    setdetailsInterval ({ state, dispatch, commit }, interval = {}) {
      // const from = interval.from || state.detailsInterval.from
      // const to = interval.to || state.detailsInterval.to

      // commit('setdetailsInterval', { from, to })
      // dispatch('getList', state.detailsInterval)
    },

    async getList ({ commit, state }) {
      commit('setLoading', true)
      try {
        const { data } = await api.get('cash.transaction.getList', {
          params: state.queryParams
        })
        commit('setItems', data)
        setTimeout(() => {
          commit('setLoading', false)
        }, 1000)
      } catch (e) {}
    },

    async getChartData ({ commit, state }) {
      const params = { ...state.queryParams }
      params.group_by = 'day'
      try {
        const { data } = await api.get('cash.aggregate.getChartData', {
          params
        })
        commit('setChartData', data)
      } catch (e) {}
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
