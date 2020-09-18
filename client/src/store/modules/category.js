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
    },
    detailsDataGenerator: state => {
      function randomInteger (min, max, count = 0) {
        if (count > 20) {
          min = -min
          max = -max
        }
        if (count > 80) {
          min = -min
          max = -max
        }
        const rand = min - 0.5 + Math.random() * (max - min + 1)
        return Math.round(rand)
      }

      const income = [...state.categories.filter(e => e.type === 'income')].sort(() => Math.random() - 0.5).slice(0, 6).map(e => {
        return {
          category_id: e.id,
          category_name: e.name,
          category_color: e.color,
          amount: randomInteger(1000, 4000)
        }
      })

      const expense = [...state.categories.filter(e => e.type === 'expense')].sort(() => Math.random() - 0.5).slice(0, 6).map(e => {
        return {
          category_id: e.id,
          category_name: e.name,
          category_color: e.color,
          amount: randomInteger(1000, 4000)
        }
      })

      const result = {
        date: '',
        income: {
          total: 2343545,
          items: income
        },
        expense: {
          total: 4349545,
          items: expense
        },
        balance: {
          total: 85948367
        }
      }

      return result
    }
  },
  mutations: {
    setCategories (state, data) {
      state.categories = data
    }
  },
  actions: {
    async getList ({ commit }, params) {
      const { data } = await api.get('cash.category.getList')
      commit('setCategories', data)
    }
  }
}
