import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    selectedTransactionsIds: []
  }),

  getters: {
    isSelected: state => id => {
      return state.selectedTransactionsIds.includes(id)
    }
  },

  mutations: {
    select (state, data) {
      state.selectedTransactionsIds = data.reduce((acc, i) => {
        if (!acc.includes(i)) {
          acc.push(i)
        }
        return acc
      }, state.selectedTransactionsIds)
    },

    unselect (state, data) {
      state.selectedTransactionsIds = data.reduce((acc, i) => {
        const ri = acc.indexOf(i)
        if (ri > -1) {
          acc.splice(ri, 1)
        }
        return acc
      }, state.selectedTransactionsIds)
    },

    empty (state) {
      state.selectedTransactionsIds = []
    }
  },

  actions: {
    async bulkDelete ({ state, commit }) {
      await api.post('cash.transaction.bulkDelete', { ids: state.selectedTransactionsIds })
      commit('empty')
    },

    async bulkMove ({ commit }, params) {
      await api.post('cash.transaction.bulkMove', params)
      commit('empty')
    }
  }

}
