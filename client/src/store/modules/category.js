import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    categories: []
  }),

  getters: {
    getById: state => id => {
      return state.categories.find(category => category.id === id)
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
      dispatch('getList')
    },

    async sort ({ commit }, params) {
      await api.post('cash.category.sort', params)
    }
  }
}
