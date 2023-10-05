import api from '@/plugins/api'
import { moment } from '@/plugins/numeralMoment'

export default {
  namespaced: true,

  state: () => ({
    balanceFlow: []
  }),

  getters: {
    getBalanceFlowByCode: state => code => {
      return state.balanceFlow.find(c => c.currency === code)
    }
  },

  mutations: {
    setBalanceFlow (state, data) {
      state.balanceFlow = data
    }
  },

  actions: {
    async getBalanceFlow ({ commit }) {
      try {
        const { data } = await api.get('cash.aggregate.getBalanceFlow', {
          params: {
            from: moment().add(-1, 'M').format('YYYY-MM-DD'),
            to: moment().add(3, 'M').format('YYYY-MM-DD'),
            group_by: 'day'
          }
        })
        commit('setBalanceFlow', data)
      } catch (_) {
        return false
      }
    }
  }

}
