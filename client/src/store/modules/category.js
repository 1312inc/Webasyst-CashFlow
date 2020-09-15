import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    categories: []
  }),
  getters: {
    getCategoryNameById: state => id => {
      const cat = state.categories.find(category => category.id === id)
      return cat ? cat.name : ''
    }
  },
  mutations: {
    'SET_ITEMS' (state, data) {
      state.categories = data
    }
  },
  actions: {
    async getList ({ commit }, params) {
      const { data } = await api.get('cash.category.getList')
      commit('SET_ITEMS', data)
    }
  }
}
