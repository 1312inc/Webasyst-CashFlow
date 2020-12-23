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
    }
  },

  actions: {
    async getList ({ commit }) {
      const { data } = await api.get('cash.account.getList')
      commit('setAccounts', data)
    },

    async update ({ dispatch }, params) {
      const method = params.id ? 'update' : 'create'
      const { data } = await api.post(`cash.account.${method}`, params)
      if (parseInt(params.starting_balance) !== 0 && !isNaN(parseInt(params.starting_balance))) {
        dispatch('transaction/update', {
          id: null,
          amount: params.starting_balance,
          date: moment().format('YYYY-MM-DD'),
          account_id: data.id,
          category_id: parseInt(params.starting_balance) > 0 ? -1 : -2,
          description: i18n.t('startingBalance'),
          silent: true
        }, { root: true })
      }
      dispatch('getList')
    },

    async delete ({ commit }, id) {
      await api.delete('cash.account.delete', {
        params: { id }
      })
      // await dispatch('getList')
      commit('deleteAccount', id)
    },

    async sort ({ commit }, params) {
      await api.post('cash.account.sort', params)
    }
  }
}
