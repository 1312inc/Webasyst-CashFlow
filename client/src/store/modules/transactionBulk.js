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
      await api.post('cash.transaction.bulkDelete', { ids: ids })
      // Remove transactions from the store
      ids.forEach(id => {
        commit('transaction/deleteTransaction', id, { root: true })
        commit('transaction/deleteCreatedTransaction', [id], { root: true })
      })
      commit('emptySelectedTransactionsIds')
      dispatch('account/getList', null, { root: true })
    },

    async bulkMove ({ dispatch, commit }, params) {
      await api.post('cash.transaction.bulkMove', params)
      commit('emptySelectedTransactionsIds')
      dispatch('account/getList', null, { root: true })
    }
  }

}
