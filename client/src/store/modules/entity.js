export default {
  namespaced: true,

  state: () => ({
    entity: null
  }),

  mutations: {
    setEntity (state, data) {
      state.entity = data
    },

    resetEntity (state) {
      state.entity = null
    }
  }
}
