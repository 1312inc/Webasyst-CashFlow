import Vue from 'vue'
import Vuex from 'vuex'
import transaction from './modules/transaction'
import category from './modules/category'
import account from './modules/account'
import system from './modules/system'
import moment from 'moment'

Vue.use(Vuex)

const setIntervalDate = (days, interval) => {
  return moment().add(days, interval).format('YYYY-MM-DD')
}

export default new Vuex.Store({
  state: () => ({
    currentType: '',
    currentTypeId: null,
    intervals: {
      from: [
        {
          key: '-1_M',
          value: setIntervalDate(-1, 'M')
        },
        {
          key: '-3_M',
          value: setIntervalDate(-3, 'M')
        },
        {
          key: '-6_M',
          value: setIntervalDate(-6, 'M')
        },
        {
          key: '-1_Y',
          value: setIntervalDate(-1, 'Y')
        },
        {
          key: '-3_Y',
          value: setIntervalDate(-3, 'Y')
        },
        {
          key: '-5_Y',
          value: setIntervalDate(-5, 'Y')
        },
        {
          key: '-10_Y',
          value: setIntervalDate(-10, 'Y')
        }
      ],
      to: [
        {
          key: '0_M',
          value: setIntervalDate(0, 'd')
        },
        {
          key: '1_M',
          value: setIntervalDate(1, 'M')
        },
        {
          key: '3_M',
          value: setIntervalDate(3, 'M')
        },
        {
          key: '6_M',
          value: setIntervalDate(6, 'M')
        },
        {
          key: '1_Y',
          value: setIntervalDate(1, 'Y')
        },
        {
          key: '2_Y',
          value: setIntervalDate(2, 'Y')
        },
        {
          key: '3_Y',
          value: setIntervalDate(3, 'Y')
        }
      ]
    }
  }),

  getters: {
    getCurrentType (state, getters) {
      const getter = getters[`${state.currentType}/getById`]
      return getter ? getter(state.currentTypeId) : {}
    }
  },

  mutations: {
    setCurrentType (state, { name, id }) {
      state.currentType = name
      state.currentTypeId = id
    }
  },

  actions: {
    updateCurrentType ({ commit, getters }, { name, id }) {
      commit('setCurrentType', { name, id })
      if (name && id) {
        commit('transaction/updateQueryParams', { filter: `${name}/${id}`, offset: 0 })
      } else {
        if (getters['account/currenciesInAccounts'][0]) {
          commit('transaction/updateQueryParams', { filter: `currency/${getters['account/currenciesInAccounts'][0]}`, offset: 0 })
        }
      }
    }
  },

  modules: {
    transaction,
    category,
    account,
    system
  }
})
