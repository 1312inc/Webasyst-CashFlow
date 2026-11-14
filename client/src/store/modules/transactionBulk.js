// TODO: Refactor Select/Unselect

import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    selectedTransactionsIds: [],
    lastCheckboxIndex: -1,
    removalPending: false,
    restorePending: false
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
    },

    setRemovalPending (state, pending) {
      state.removalPending = pending
    },

    setRestorePending (state, pending) {
      state.restorePending = pending
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

    async bulkDelete ({ state, commit }, ids = [...state.selectedTransactionsIds]) {
      try {
        await api.post('cash.transaction.bulkDelete', { ids })
        // Remove transactions from the store
        commit('transaction/deleteTransaction', ids, { root: true })
        commit('unselect', ids)
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
      if (state.removalPending || state.restorePending || !state.selectedTransactionsIds.length) return
      const ids = [...state.selectedTransactionsIds]
      commit('setRestorePending', true)
      try {
        await api.post('cash.transaction.restore', { ids })
        commit('unselect', ids)
      } finally {
        commit('setRestorePending', false)
      }
    },

    async purge ({ state, commit }, ids = [...state.selectedTransactionsIds]) {
      await api.post('cash.transaction.purge', { ids })
      commit('unselect', ids)
    }
  }

}
