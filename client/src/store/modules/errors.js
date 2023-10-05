export default {
  namespaced: true,

  state: () => ({
    errors: []
  }),

  getters: {

  },

  mutations: {
    error (state, data) {
      state.errors.push(data)
    },

    delete (state, index) {
      state.errors.splice(index, 1)
    }
  },

  actions: {

  }

}
