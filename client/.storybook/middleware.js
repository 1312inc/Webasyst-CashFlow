const proxy = require('http-proxy-middleware')
const fs = require('fs')
const dotenv = require('dotenv')
const envConfig = dotenv.parse(fs.readFileSync('.env.desktop.development.local'))
for (const k in envConfig) {
  process.env[`STORYBOOK_${k}`] = envConfig[k]
}

module.exports = function expressMiddleware (router) {
  router.use('/api.php', proxy({
    target: process.env.STORYBOOK_VUE_APP_DEV_PROXY,
    changeOrigin: true
  }))
}