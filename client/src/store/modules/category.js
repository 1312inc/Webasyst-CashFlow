import router from '../../router'
import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    categories: window.appState?.categories || []
  }),

  getters: {
    sortedCategories: (state) => {
      const initialAcc = state.categories
        .sort((a, b) => {
          if (a.sort > b.sort) {
            return 1
          }
          if (a.sort < b.sort) {
            return -1
          }
          return 0
        })

      return initialAcc.reduce((acc, cat) => {
        if (cat.parent_category_id !== null) {
          const currentIndex = acc.findIndex(c => c.id === cat.id)
          acc.splice(currentIndex, 1)
          const parentIndex = acc.findIndex(c => c.id === cat.parent_category_id)
          acc.splice(parentIndex + 1, 0, cat)
        }
        return acc
      }, [...initialAcc])
    },

    getById: state => id => {
      return state.categories.find(category => category.id === id)
    },

    getByType: (state, getters) => category => {
      return getters.sortedCategories.filter(e => e.type === category)
    }
  },

  mutations: {
    setCategories (state, data) {
      state.categories = data
    },

    updateCategory (state, data) {
      const index = state.categories.findIndex(c => c.id === data.id)
      if (index > -1) {
        state.categories.splice(index, 1, data)
      }
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

    async update ({ commit }, params) {
      const method = params.id ? 'update' : 'create'
      try {
        const { data } = await api.post(`cash.category.${method}`, params)
        commit('updateCategory', data)
        if (method === 'create') {
          router.push({ name: 'Category', params: { id: data.id, isFirtsTimeNavigate: true } })
        }
      } catch (_) {
        return false
      }
    },

    async delete ({ dispatch, commit }, id) {
      try {
        await api.post('cash.category.delete', { id })
        commit('transaction/resetTransactions', null, { root: true })
        dispatch('getList')
          .then(() => {
            router.push({ name: 'Home' })
          })
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
    }
  }
}
