import router from '../../router'
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
    },

    accountsByCurrencyCode: state => code => {
      return state.accounts.filter(a => a.currency === code).map(a => a.id)
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
        const { data } = await api.post(`cash.account.${method}`, params)
        // emitting for the mobile platform
        if (this.$isSpaMobileMode && method === 'create') {
          window.emitter.emit('createAccount', data)
        }
        // make transaction if starting balance added
        if (parseInt(params.starting_balance) !== 0 && !isNaN(parseInt(params.starting_balance))) {
          await dispatch('transaction/update', {
            id: null,
            amount: params.starting_balance,
            date: moment().format('YYYY-MM-DD'),
            account_id: data.id,
            category_id: parseInt(params.starting_balance) >= 0 ? -2 : -1,
            description: i18n.t('startingBalance'),
            silent: true // fetch silently
          }, { root: true })
        }
        dispatch('getList')
          .then(() => {
            // redirect to the entity page if new one
            if (method === 'create') {
              router.push({ name: 'Account', params: { id: data.id, isFirtsTimeNavigate: true } })
            }
          })
      } catch (_) {
        return false
      }
    },

    async delete ({ dispatch, commit }, id) {
      try {
        await api.post('cash.account.delete', { id })
        commit('transaction/resetTransactions', null, { root: true })
        dispatch('getList')
          .then(() => {
            router.push({ name: 'Home' })
          })
      } catch (_) {
        return false
      }
    },

    async sort ({ commit }, params) {
      try {
        commit('updateSort', params.newOrder)
        await api.post('cash.account.sort', {
          order: params.newOrder
        })
      } catch (_) {
        commit('updateSort', params.oldOrder)
        return false
      }
    }
  }
}
