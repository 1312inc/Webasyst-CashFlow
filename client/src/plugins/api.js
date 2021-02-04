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
  if (response.data.error && response.data.error === 'error') {
    store.commit('errors/error', response.data.error_message)
    return Promise.reject(new Error(response.data.error_message))
  }
  return response
}, (error) => {
  store.commit('errors/error', error)
  return Promise.reject(error)
})

export { baseApiUrl, accessToken }

export default api
