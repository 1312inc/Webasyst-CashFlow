import Vue from 'vue'
import Vuex from 'vuex'
import balanceFlow from './modules/balanceFlow'
import transaction from './modules/transaction'
import transactionBulk from './modules/transactionBulk'
import category from './modules/category'
import account from './modules/account'
import system from './modules/system'
import entity from './modules/entity'
import errors from './modules/errors'
import mediator from './store-mediator'

Vue.use(Vuex)

export default new Vuex.Store({
  state: () => ({
    currentType: '',
    currentTypeId: null,
    multiSelectMode: process.env.VUE_APP_MODE !== 'mobile'
  }),

  getters: {
    getCurrentType (state, getters) {
      const getter = getters[`${state.currentType}/getById`]
      return getter ? getter(state.currentTypeId) : {}
    }
  },

  mutations: {
    setCurrentEntity (state, { name, id }) {
      state.currentType = name
      state.currentTypeId = id
    },

    setMultiSelectMode (state, data) {
      state.multiSelectMode = data
    }
  },

  actions: {
    updateCurrentEntity ({ commit, getters }, { name, id }) {
      if (name && id) {
        commit('setCurrentEntity', { name, id })
        commit('transaction/updateQueryParams', { filter: `${name}/${id}` })
      } else {
        // if Home page
        const defaultCurrency = getters['account/currenciesInAccounts'][0]
        if (defaultCurrency) {
          commit('setCurrentEntity', { name: 'currency', id: defaultCurrency })
          commit('transaction/updateQueryParams', { filter: `currency/${defaultCurrency}` })
        }
      }
    }
  },

  modules: {
    balanceFlow,
    transaction,
    transactionBulk,
    category,
    account,
    system,
    entity,
    errors
  },

  plugins: [mediator]
})
