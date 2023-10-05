import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    selectedTransactionsIds: [],
    lastCheckboxIndex: -1
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
    },

    setLastCheckboxIndex (state, index) {
      state.lastCheckboxIndex = index
    }
  },

  actions: {
    async bulkCreate ({ commit }, transactions) {
      try {
        const { data: createdTransactions } = await api.post(
          'cash.transaction.bulkCreate',
          transactions
        )
        commit('transaction/createTransactions', createdTransactions, {
          root: true
        })
      } catch (_) {
        return false
      }
    },

    async bulkDelete ({ dispatch, state, commit }) {
      const ids = state.selectedTransactionsIds
      try {
        await api.post('cash.transaction.bulkDelete', { ids: ids })
        // Remove transactions from the store
        commit('transaction/deleteTransaction', ids, { root: true })
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
    },

    async restore ({ state, commit }) {
      const ids = state.selectedTransactionsIds
      await api.post('cash.transaction.restore', { ids })
      commit('emptySelectedTransactionsIds')
    },

    async purge ({ state, commit }) {
      const ids = state.selectedTransactionsIds
      await api.post('cash.transaction.purge', { ids })
      commit('emptySelectedTransactionsIds')
    }
  }

}
