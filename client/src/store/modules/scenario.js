import api from '@/plugins/api'
import { appStateService } from '@/services/appState'
import { scenarioContextService } from '@/services/scenarioContext'

export default {
  namespaced: true,

  state: () => ({
    scenarios: []
  }),

  getters: {
    sortedScenarios: state => {
      return [...state.scenarios].sort((a, b) => a.sort - b.sort)
    },

    getById: state => id => {
      return state.scenarios.find(scenario => scenario.id === id)
    }
  },

  mutations: {
    setScenarios (state, data) {
      state.scenarios = data
    },

    addScenario (state, data) {
      state.scenarios.push(data)
    },

    updateScenario (state, data) {
      const index = state.scenarios.findIndex(s => s.id === data.id)
      if (index > -1) {
        state.scenarios.splice(index, 1, data)
      }
    },

    removeScenario (state, id) {
      state.scenarios = state.scenarios.filter(s => s.id !== id)
    },

    reorderScenarios (state, order) {
      order.forEach((id, sort) => {
        const scenario = state.scenarios.find(s => s.id === id)
        if (scenario) {
          scenario.sort = sort
        }
      })
      state.scenarios.sort((a, b) => a.sort - b.sort)
    }
  },

  actions: {
    async getList ({ commit }) {
      if (!appStateService.isPremium) {
        return false
      }
      try {
        const { data } = await api.get('cash.scenario.getList')
        if (Array.isArray(data)) {
          commit('setScenarios', data.sort((a, b) => a.sort - b.sort))
        }
      } catch (_) {
        return false
      }
    },

    async create ({ commit, state }, { name, color }) {
      try {
        const { data } = await api.post('cash.scenario.create', {
          name,
          color,
          sort: state.scenarios.length
        })
        commit('addScenario', data)
        return data
      } catch (_) {
        return false
      }
    },

    async update ({ commit, getters }, params) {
      const item = getters.getById(params.id)
      const stateBefore = { ...item }
      try {
        const reqParams = { ...item, ...params }
        commit('updateScenario', reqParams)
        const { data } = await api.post('cash.scenario.update', reqParams)
        commit('updateScenario', data)
        return data
      } catch (_) {
        commit('updateScenario', stateBefore)
        return false
      }
    },

    async delete ({ commit }, id) {
      try {
        await api.post('cash.scenario.delete', { id })
        commit('removeScenario', id)
        if (scenarioContextService.scenarioId === id) {
          scenarioContextService.scenarioId = 0
        }
      } catch (_) {
        return false
      }
    },

    async sort ({ commit, getters }, { oldOrder, newOrder }) {
      const oldSortMap = {}
      oldOrder.forEach((id, index) => {
        oldSortMap[id] = index
      })

      commit('reorderScenarios', newOrder)
      try {
        const updates = newOrder
          .map((id, sort) => {
            if (oldSortMap[id] === sort) {
              return null
            }
            const scenario = getters.getById(id)
            return api.post('cash.scenario.update', {
              id,
              name: scenario.name,
              color: scenario.color,
              sort
            })
          })
          .filter(Boolean)
        if (updates.length) {
          await Promise.all(updates)
        }
      } catch (_) {
        commit('reorderScenarios', oldOrder)
        return false
      }
    }
  }
}
