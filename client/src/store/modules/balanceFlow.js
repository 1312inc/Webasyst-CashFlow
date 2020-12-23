import api from '@/plugins/api'
import { moment } from '@/plugins/numeralMoment'

export default {
  namespaced: true,

  state: () => ({
    balanceFlow: []
  }),

  getters: {
    getBalanceFlowByCode: state => code => {
      return state.balanceFlow.find(c => c.currency === code) || state.balanceFlow[0]
    }
  },

  mutations: {
    setBalanceFlow (state, data) {
      state.balanceFlow = data
    }
  },

  actions: {
    async getBalanceFlow ({ commit }) {
      const { data } = await api.get('cash.aggregate.getBalanceFlow', {
        params: {
          from: moment().add(-7, 'd').format('YYYY-MM-DD'),
          to: moment().add(1, 'M').format('YYYY-MM-DD'),
          group_by: 'day'
        }
      })
      commit('setBalanceFlow', data)
    }
  }

}
