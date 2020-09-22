import axios from 'axios'

export default axios.create({
  baseURL: process.env.VUE_APP_BASE_URL || (window.appState ? `${window.appState.baseUrl}api.php` : ''),
  params: {
    access_token: process.env.VUE_APP_API_TOKEN || (window.appState ? window.appState.token : '')
  }
})
