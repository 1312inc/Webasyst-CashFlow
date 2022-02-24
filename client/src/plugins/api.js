import axios from 'axios'
import store from '../store'
import { i18n } from './locale'

const baseApiUrl = window.appState?.baseApiUrl || ''
const accessToken = window.appState?.token || ''

const api = axios.create({
  baseURL: baseApiUrl,
  params: {
    access_token: accessToken
  },
  headers: {
    'Content-Type': 'application/json'
  }
})

api.interceptors.response.use((response) => {
  if (!response.headers['content-type'].includes('application/json')) {
    store.commit('errors/error', {
      title: 'error.api',
      method: i18n.t('error.nonJsonTitle'),
      message: i18n.t('error.nonJsonText')
    })
  }
  if (response.data?.error) {
    store.commit('errors/error', {
      title: 'error.api',
      method: response.config.url,
      message: response.data.error_description
    })
  }
  return response
}, (error) => {
  if (!error.response.headers['content-type'].includes('application/json')) {
    store.commit('errors/error', {
      title: 'error.api',
      method: error.response.config.url,
      message: i18n.t('error.nonJsonTitle')
    })
    return Promise.reject(error)
  }
  if (error.response.data?.error) {
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
