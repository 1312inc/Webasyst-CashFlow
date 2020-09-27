import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    categories: []
  }),

  getters: {
    getCategoryById: state => id => {
      return state.categories.find(category => category.id === id)
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

      const income = [...state.categories.filter(e => e.type === 'income')]
        .sort(() => Math.random() - 0.5)
        .slice(0, 6)
        .map(e => {
          return {
            category_id: e.id,
            category_name: e.name,
            category_color: e.color,
            amount: randomInteger(1000, 4000)
          }
        })

      const expense = [...state.categories.filter(e => e.type === 'expense')]
        .sort(() => Math.random() - 0.5)
        .slice(0, 6)
        .map(e => {
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
    },

    updateItem (state, data) {
      let start = 0
      let deleteCount = 0

      const itemIndex = state.categories.findIndex(e => e.id === data.id)
      if (itemIndex > -1) {
        start = itemIndex
        deleteCount = 1
      }

      state.categories.splice(start, deleteCount, data)
    },

    deleteItem (state, id) {
      const itemIndex = state.categories.findIndex(e => e.id === id)
      if (itemIndex > -1) {
        state.categories.splice(itemIndex, 1)
      }
    }
  },

  actions: {
    async getList ({ commit }) {
      const { data } = await api.get('cash.category.getList')
      commit('setCategories', data)
    },

    async update ({ commit }, params) {
      const method = params.id ? 'update' : 'create'

      const formData = new FormData()
      for (const key in params) {
        formData.append(key, params[key])
      }

      const { data } = await api.post(`cash.category.${method}`, formData)

      commit('updateItem', data)
    },

    async delete ({ commit }, id) {
      await api.delete('cash.category.delete', {
        params: { id }
      })

      commit('deleteItem', id)
    }
  }
}
