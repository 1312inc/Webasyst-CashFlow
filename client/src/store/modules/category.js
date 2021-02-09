import api from '@/plugins/api'
import router from '@/router'

export default {
  namespaced: true,

  state: () => ({
    categories: window.appState?.categories || []
  }),

  getters: {
    getById: state => id => {
      return state.categories.find(category => category.id === id)
    },

    getByType: state => category => {
      return state.categories
        .filter(e => e.type === category)
        .sort((a, b) => {
          if (a.sort > b.sort) {
            return 1
          }
          if (a.sort < b.sort) {
            return -1
          }
          return 0
        })
    }
  },

  mutations: {
    setCategories (state, data) {
      state.categories = data
    },

    updateSort (state, data) {
      data.forEach((id, i) => {
        state.categories.find(c => c.id === id).sort = i
      })
    }
  },

  actions: {
    async getList ({ commit }) {
      try {
        const { data } = await api.get('cash.category.getList')
        commit('setCategories', data)
      } catch (_) {
        return false
      }
    },

    async update ({ dispatch }, params) {
      const method = params.id ? 'update' : 'create'
      try {
        await api.post(`cash.category.${method}`, params)
        dispatch('getList')
      } catch (_) {
        return false
      }
    },

    async delete ({ dispatch }, id) {
      try {
        await api.delete('cash.category.delete', {
          params: { id }
        })
        router.push({ name: 'Home' })
        await dispatch('getList')
        dispatch('emitCategoryStateUpdate')
      } catch (_) {
        return false
      }
    },

    sort ({ commit }, params) {
      try {
        commit('updateSort', params.order)
        api.post('cash.category.sort', params)
      } catch (_) {
        return false
      }
    },

    emitCategoryStateUpdate ({ dispatch }) {
      dispatch('account/getList', null, { root: true })
      dispatch('balanceFlow/getBalanceFlow', null, { root: true })
    }
  }
}
