import axios from 'axios'
import store from '../store'
import { i18n } from './locale'
import { companyContextService } from '@/services/companyContext'
import { appStateService } from '@/services/appState'

const baseApiUrl = appStateService.baseApiUrl
const accessToken = appStateService.token

const api = axios.create({
  baseURL: baseApiUrl,
  params: {
    access_token: accessToken
  },
  headers: {
    'Content-Type': 'application/json'
  }
})

const companyQueryMethods = /^cash\.(account\.getList|transaction\.getList|aggregate\.|plan\.get$)/
const companyBodyMethods = /^cash\.plan\.set$/

api.interceptors.request.use((config) => {
  const companyId = companyContextService.companyId
  if (!companyId) return config

  const method = config.url?.split('?')[0]
  if (!method) return config

  if (companyQueryMethods.test(method)) {
    config.params = {
      ...config.params,
      company_id: companyId
    }
  }

  if (companyBodyMethods.test(method)) {
    const data = typeof config.data === 'string' ? JSON.parse(config.data) : (config.data || {})
    config.data = {
      ...data,
      company_id: companyId
    }
  }

  return config
})

api.interceptors.response.use((response) => {
  if (response.status === 200 && !response.headers['content-type']?.includes('application/json')) {
    store.commit('errors/error', {
      title: 'error.api',
      method: i18n.t('error.nonJsonTitle'),
      message: i18n.t('error.nonJsonText')
    })
  }
  return response
}, (error) => {
  if (!error.response) {
    return Promise.reject(error)
  }
  // show premium modal
  if (error.response.status === 402) {
    return Promise.reject(error)
  }
  if (!error.response.headers['content-type']?.includes('application/json')) {
    store.commit('errors/error', {
      title: 'error.api',
      method: error.response.config.url,
      message: i18n.t('error.nonJsonTitle')
    })
    return Promise.reject(error)
  }
  if (error.response.data?.error_description) {
    store.commit('errors/error', {
      title: 'error.api',
      method: error.response.config.url,
      message: error.response.data.error_description
    })
  } else {
    store.commit('errors/error', {
      title: 'error.http',
      method: error.response.config.url,
      message: error.response.data
    })
  }
  return Promise.reject(error)
})

export { baseApiUrl, accessToken }

export default api
