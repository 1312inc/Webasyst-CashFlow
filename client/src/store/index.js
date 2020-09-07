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
    items: []
  },
  mutations: {
    'SET_ITEMS' (store, data) {
      store.items = data
    }
  },
  actions: {
    async get ({ commit }) {
      const { data } = await api.get('cash.transaction.getList', {
        params: {
          from: '2020-09-09',
          to: '2020-09-09'
        }
      })
      commit('SET_ITEMS', data)
    }
  },
  modules: {
  }
})
