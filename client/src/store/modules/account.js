import api from '@/plugins/api'
import { moment } from '@/plugins/numeralMoment'
import { i18n } from '@/plugins/locale'
import router from '@/router'

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

    async delete ({ dispatch }, id) {
      await api.delete('cash.account.delete', {
        params: { id }
      })
      router.push({ name: 'Home' })
      dispatch('getList')
    },

    async sort ({ commit }, params) {
      await api.post('cash.account.sort', params)
    }
  }
}
