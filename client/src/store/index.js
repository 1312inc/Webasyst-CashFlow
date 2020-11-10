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
          title: 'Last 30 days',
          value: setIntervalDate(-1, 'M')
        },
        {
          title: 'Last 90 days',
          value: setIntervalDate(-3, 'M')
        },
        {
          title: 'Last 180 days',
          value: setIntervalDate(-6, 'M')
        },
        {
          title: 'Last 365 days',
          value: setIntervalDate(-1, 'Y')
        },
        {
          title: 'Last 3 years',
          value: setIntervalDate(-3, 'Y')
        },
        {
          title: 'Last 5 years',
          value: setIntervalDate(-5, 'Y')
        },
        {
          title: 'Last 10 years',
          value: setIntervalDate(-10, 'Y')
        }

      ],
      to: [
        {
          title: 'None',
          value: setIntervalDate(0, 'd')
        },
        {
          title: 'Future 30 days',
          value: setIntervalDate(1, 'M')
        },
        {
          title: 'Future 90 days',
          value: setIntervalDate(3, 'M')
        },
        {
          title: 'Future 180 days',
          value: setIntervalDate(6, 'M')
        },
        {
          title: 'Future 365 days',
          value: setIntervalDate(1, 'Y')
        },
        {
          title: 'Future 2 years',
          value: setIntervalDate(2, 'Y')
        },
        {
          title: 'Future 3 years',
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
