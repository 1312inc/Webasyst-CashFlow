import axios from 'axios'

const baseApiUrl = window?.appState?.baseApiUrl || '/api.php'
const accessToken = process.env.VUE_APP_API_TOKEN || window?.appState?.token || ''

export { baseApiUrl, accessToken }

export default axios.create({
  baseURL: baseApiUrl,
  params: {
    access_token: accessToken
  },
  headers: {
    'Content-Type': 'application/json'
  }
})
