import axios from 'axios'
import store from '../store'
import { locale } from './locale'

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
      method: locale === 'ru_RU' ? 'Что-то не так с API (api.php). Ответ не в формате JSON' : "Something's wrong with the API (api.php). Not a JSON response",
      message: locale === 'ru_RU' ? 'Пожалуйста, свяжитесь со своим хостинг-провайдером или администратором, который настраивал вам Webasyst' : 'Please contact your hosting provider or admin who had been configuring Webasyst on your server'
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
