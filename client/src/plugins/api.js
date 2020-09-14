import axios from 'axios'

export default axios.create({
  baseURL: process.env.VUE_APP_BASE_URL,
  params: {
    access_token: process.env.VUE_APP_API_TOKEN || (document.querySelector('meta[name="token"]') ? document.querySelector('meta[name="token"]').content : '')
  }
})
