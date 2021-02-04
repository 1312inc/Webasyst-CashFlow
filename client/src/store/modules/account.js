import api from '@/plugins/api'
import { moment } from '@/plugins/numeralMoment'
import { i18n } from '@/plugins/locale'

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

    deleteAccount (state, id) {
      const i = state.accounts.findIndex(a => a.id === id)
      if (i > -1) {
        state.accounts.splice(i, 1)
      }
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
        const { data } = await api.post(`cash.account.${method}`, params)
        if (parseInt(params.starting_balance) !== 0 && !isNaN(parseInt(params.starting_balance))) {
          await dispatch('transaction/update', {
            id: null,
            amount: params.starting_balance,
            date: moment().format('YYYY-MM-DD'),
            account_id: data.id,
            category_id: parseInt(params.starting_balance) >= 0 ? -2 : -1,
            description: i18n.t('startingBalance'),
            silent: true
          }, { root: true })
        }
        dispatch('getList')
      } catch (_) {
        return false
      }
    },

    async delete ({ commit }, id) {
      try {
        await api.delete('cash.account.delete', {
          params: { id }
        })
        commit('deleteAccount', id)
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
