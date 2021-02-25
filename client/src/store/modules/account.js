import router from '../../router'
import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    accounts: window.appState?.accounts || []
  }),

  getters: {
    getById: state => id => {
      return state.accounts.find(account => account.id === id)
    },

    currenciesInAccounts: state => {
      return state.accounts.reduce((acc, { currency }) => {
        if (!acc.includes(currency)) acc.push(currency)
        return acc
      }, [])
    }
  },

  mutations: {
    setAccounts (state, data) {
      state.accounts = data
    },

    updateSort (state, ids) {
      state.accounts.sort((a, b) => {
        return ids.indexOf(a.id) - ids.indexOf(b.id)
      })
    }
  },

  actions: {
    async getList ({ commit }) {
      try {
        const { data } = await api.get('cash.account.getList')
        commit('setAccounts', data)
      } catch (_) {
        return false
      }
    },

    async update ({ dispatch }, params) {
      const method = params.id ? 'update' : 'create'
      try {
        await api.post(`cash.account.${method}`, params)
        dispatch('getList')
      } catch (_) {
        return false
      }
    },

    async delete ({ dispatch }, id) {
      try {
        await api.delete('cash.account.delete', {
          params: { id }
        })
        dispatch('getList')
          .then(() => {
            router.push({ name: 'Home' })
          })
      } catch (_) {
        return false
      }
    },

    sort ({ commit }, params) {
      try {
        commit('updateSort', params.order)
        api.post('cash.account.sort', params)
      } catch (_) {
        return false
      }
    }
  }
}
