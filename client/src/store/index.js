import Vue from 'vue'
import Vuex from 'vuex'
import transaction from './modules/transaction'
import category from './modules/category'
import account from './modules/account'

Vue.use(Vuex)

export default new Vuex.Store({
  state: () => ({
    currentType: '',
    currentTypeId: null
  }),

  getters: {
    getCurrentType (state, getters) {
      const getter = getters[`${state.currentType.toLowerCase()}/getById`]
      return getter ? getter(state.currentTypeId) : undefined
    }
  },

  mutations: {
    setCurrentType (state, { name = '', id = null }) {
      state.currentType = name
      state.currentTypeId = id
    }
  },

  modules: {
    transaction,
    category,
    account
  }
})
