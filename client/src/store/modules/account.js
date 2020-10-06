import api from '@/plugins/api'

export default {
  namespaced: true,

  state: () => ({
    accounts: []
  }),

  getters: {
    getById: state => id => {
      return state.accounts.find(account => account.id === id)
    }
  },

  mutations: {
    setAccounts (state, data) {
      state.accounts = data
    },

    updateItem (state, data) {
      let start = 0
      let deleteCount = 0

      const itemIndex = state.accounts.findIndex(e => e.id === data.id)
      if (itemIndex > -1) {
        start = itemIndex
        deleteCount = 1
      }

      state.accounts.splice(start, deleteCount, data)
    },

    deleteItem (state, id) {
      const itemIndex = state.accounts.findIndex(e => e.id === id)
      if (itemIndex > -1) {
        state.accounts.splice(itemIndex, 1)
      }
    }

  },

  actions: {
    async getList ({ commit }) {
      const { data } = await api.get('cash.account.getList')
      commit('setAccounts', data)
    },

    async update ({ commit }, params) {
      const method = params.id ? 'update' : 'create'

      const formData = new FormData()
      for (const key in params) {
        formData.append(key, params[key])
      }

      const { data } = await api.post(`cash.account.${method}`, formData)

      commit('updateItem', data)
    },

    async delete ({ commit }, id) {
      await api.delete('cash.account.delete', {
        params: { id }
      })

      commit('deleteItem', id)
    }
  }
}
