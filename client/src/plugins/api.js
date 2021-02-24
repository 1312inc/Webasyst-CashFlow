import axios from 'axios'
import store from '../store'

const baseApiUrl = window?.appState?.baseApiUrl || '/api.php'
const accessToken = process.env.VUE_APP_API_TOKEN || window?.appState?.token || ''

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
  if (response.data.error) {
    store.commit('errors/error', {
      title: 'error.api',
      method: response.config.url,
      message: response.data.error_description
    })
    return Promise.reject(new Error(response.data.error_description))
  }
  return response
}, (error) => {
  if (error.response.data.error) {
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
