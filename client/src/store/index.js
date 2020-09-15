import Vue from 'vue'
import Vuex from 'vuex'
import transaction from './modules/transaction'
import category from './modules/category'
import account from './modules/account'

Vue.use(Vuex)

export default new Vuex.Store({
  modules: {
    transaction,
    category,
    account
  }
})
