import router from '../../router'
import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    categories: window.appState?.categories || []
  }),

  getters: {
    sortedCategories: state => {
      const input = [...state.categories]
        .sort((a, b) => {
          if (a.sort > b.sort) {
            return 1
          }
          if (a.sort < b.sort) {
            return -1
          }
          return 0
        })

      const assemble = (arr, parentId = null, result = []) => {
        arr.forEach(el => {
          const parentCat = el.parent_category_id ?? null
          if (parentCat === parentId) {
            result.push(el)
            assemble(arr, el.id, result)
          }
        })
        return result
      }

      return assemble(input)
    },

    getChildren: (state, getters) => id => {
      return getters.sortedCategories.filter(c => c.parent_category_id === id)
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
      } else {
        state.categories.unshift(data)
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
          setTimeout(() => {
            router.push({ name: 'Category', params: { id: data.id, isFirtsTimeNavigate: true } })
          })
        }
      } catch (_) {
        return false
      }
    },

    async updateParams ({ commit, getters, dispatch }, params) {
      const item = getters.getById(params.id)
      const stateBefore = { ...item }
      try {
        const reqParams = { ...item, ...params }
        commit('updateCategory', reqParams)
        await api.post('cash.category.update', reqParams)
      } catch (_) {
        commit('updateCategory', stateBefore)
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

    async sort ({ commit }, params) {
      try {
        commit('updateSort', params.newOrder)
        await api.post('cash.category.sort', {
          order: params.newOrder
        })
      } catch (_) {
        commit('updateSort', params.oldOrder)
        return false
      }
    }
  }
}
