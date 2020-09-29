import axios from 'axios'

export default axios.create({
  baseURL: window?.appState?.baseApiUrl || '/api.php',
  params: {
    access_token: process.env.VUE_APP_API_TOKEN || window?.appState?.token || ''
  },
  headers: {
    'Content-Type': 'multipart/form-data'
  }
})
