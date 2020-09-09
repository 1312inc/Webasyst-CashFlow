import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'

const api = axios.create({
  baseURL: process.env.VUE_APP_BASE_URL,
  params: {
    access_token: process.env.VUE_APP_API_TOKEN || (document.querySelector('meta[name="token"]') ? document.querySelector('meta[name="token"]').content : '')
  }
})

Vue.use(Vuex)

export default new Vuex.Store({
  state: {
    listItems: []
  },
  mutations: {
    'SET_ITEMS' (store, data) {
      store.listItems = data
    }
  },
  actions: {
    async getList ({ commit }, params) {
      const { data } = await api.get('cash.transaction.getList', {
        params: {
          from: params.from,
          to: params.to
        }
      })
      commit('SET_ITEMS', data)
    }
  },
  modules: {
  }
})
