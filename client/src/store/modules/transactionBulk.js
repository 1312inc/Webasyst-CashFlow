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

    emptySelectedTransactionsIds (state) {
      state.selectedTransactionsIds = []
    }
  },

  actions: {
    async bulkDelete ({ dispatch, state, commit }) {
      const ids = state.selectedTransactionsIds
      try {
        await api.post('cash.transaction.bulkDelete', { ids: ids })
        // Remove transactions from the store
        ids.forEach(id => {
          commit('transaction/deleteTransaction', id, { root: true })
          commit('transaction/deleteCreatedTransaction', [id], { root: true })
        })
        commit('emptySelectedTransactionsIds')
      } catch (_) {
        return false
      }
    },

    async bulkMove ({ dispatch, commit }, params) {
      try {
        await api.post('cash.transaction.bulkMove', params)
        commit('emptySelectedTransactionsIds')
      } catch (_) {
        return false
      }
    }
  }

}
