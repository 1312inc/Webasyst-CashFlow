import api from '@/plugins/api'
import { appStateService } from '@/services/appState'
import { companyContextService } from '@/services/companyContext'

export default {
  namespaced: true,

  state: () => ({
    companies: []
  }),

  getters: {
    sortedCompanies: state => {
      return [...state.companies].sort((a, b) => a.sort - b.sort)
    },

    getById: state => id => {
      return state.companies.find(company => company.id === id)
    }
  },

  mutations: {
    setCompanies (state, data) {
      state.companies = data
    },

    addCompany (state, data) {
      state.companies.push(data)
    },

    updateCompany (state, data) {
      const index = state.companies.findIndex(c => c.id === data.id)
      if (index > -1) {
        state.companies.splice(index, 1, data)
      }
    },

    removeCompany (state, id) {
      state.companies = state.companies.filter(c => c.id !== id)
    },

    reorderCompanies (state, order) {
      order.forEach((id, sort) => {
        const company = state.companies.find(c => c.id === id)
        if (company) {
          company.sort = sort
        }
      })
      state.companies.sort((a, b) => a.sort - b.sort)
    }
  },

  actions: {
    async getList ({ commit }) {
      if (!appStateService.isPremium) {
        return false
      }
      try {
        const { data } = await api.get('cash.company.getList')
        if (Array.isArray(data)) {
          commit('setCompanies', data.sort((a, b) => a.sort - b.sort))
        }
      } catch (_) {
        return false
      }
    },

    async create ({ commit, state }, { name }) {
      try {
        const { data } = await api.post('cash.company.create', {
          name,
          sort: state.companies.length
        })
        commit('addCompany', data)
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
        commit('updateCompany', reqParams)
        const { data } = await api.post('cash.company.update', reqParams)
        commit('updateCompany', data)
        return data
      } catch (_) {
        commit('updateCompany', stateBefore)
        return false
      }
    },

    async delete ({ commit }, id) {
      try {
        await api.post('cash.company.delete', { id })
        commit('removeCompany', id)
        if (companyContextService.companyId === id) {
          companyContextService.companyId = 0
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

      commit('reorderCompanies', newOrder)
      try {
        const updates = newOrder
          .map((id, sort) => {
            if (oldSortMap[id] === sort) {
              return null
            }
            const company = getters.getById(id)
            return api.post('cash.company.update', { id, name: company.name, sort })
          })
          .filter(Boolean)
        if (updates.length) {
          await Promise.all(updates)
        }
      } catch (_) {
        commit('reorderCompanies', oldOrder)
        return false
      }
    }
  }
}
