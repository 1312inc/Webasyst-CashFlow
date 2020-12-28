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
      data.forEach((element, i) => {
        state.categories.find(c => c.id === element.id).sort = i
      })
    }
  },

  actions: {
    async getList ({ commit }) {
      const { data } = await api.get('cash.category.getList')
      commit('setCategories', data)
    },

    async update ({ dispatch }, params) {
      const method = params.id ? 'update' : 'create'
      await api.post(`cash.category.${method}`, params)
      dispatch('getList')
    },

    async delete ({ dispatch }, id) {
      await api.delete('cash.category.delete', {
        params: { id }
      })
      router.push({ name: 'Home' })
      await dispatch('getList')
      dispatch('account/getList', null, { root: true })
    },

    async sort ({ commit }, params) {
      await api.post('cash.category.sort', params)
    }
  }
}
