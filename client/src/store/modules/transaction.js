import api from '@/plugins/api'
import dumpDataByDay from '@/plugins/dumpDataByDay'

export default {
  namespaced: true,

  state: () => ({
    listItems: [],
    chartData: [],
    detailsDate: {
      from: null,
      to: null
    }
  }),

  getters: {
    getTransactionById: state => id => {
      return state.listItems.find(t => t.id === id)
    }
  },

  mutations: {
    setItems (state, data) {
      state.listItems = data
    },

    setChartData (state, data) {
      state.chartData = data
    },

    setDetailsDate (state, data) {
      state.detailsDate = data
    },

    updateItem (state, data) {
      data.forEach(transaction => {
        const itemIndex = state.listItems.findIndex(e => e.id === transaction.id)
        if (itemIndex > -1) {
          state.listItems.splice(itemIndex, 1, transaction)
        } else {
          state.listItems.push(transaction)
          state.listItems.sort((a, b) => {
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
      const itemIndex = state.listItems.findIndex(e => e.id === id)
      if (itemIndex > -1) {
        state.listItems.splice(itemIndex, 1)
      }
    }
  },

  actions: {
    async getList ({ commit }, params) {
      try {
        const { data } = await api.get('cash.transaction.getList', {
          params: {
            from: params.from,
            to: params.to
          }
        })
        commit('setItems', data)
      } catch (e) {}
    },

    async getChartData ({ commit }) {
      commit('setChartData', dumpDataByDay('2018-08-15', '2021-02-15'))
    },

    async setDetailsDate ({ dispatch, commit }, dates) {
      dispatch('getList', dates)
      commit('setDetailsDate', dates)
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
