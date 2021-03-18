var webpack = require('webpack')
var fetch = require('node-fetch')

fetch(process.env.VUE_APP_DEV_PROXY + '/api.php/cash.system.getCurrencies?access_token=' + process.env.VUE_APP_API_TOKEN)
  .then(response => response.text())
  .then(res => {
    process.env.VUE_APP_CURRENCIES = res
  })

fetch(process.env.VUE_APP_DEV_PROXY + '/api.php/cash.account.getList?access_token=' + process.env.VUE_APP_API_TOKEN)
  .then(response => response.text())
  .then(res => {
    process.env.VUE_APP_ACCOUNTS = res
  })

fetch(process.env.VUE_APP_DEV_PROXY + '/api.php/cash.category.getList?access_token=' + process.env.VUE_APP_API_TOKEN)
  .then(response => response.text())
  .then(res => {
    process.env.VUE_APP_CATEGORIES = res
  })

fetch(process.env.VUE_APP_DEV_PROXY + '/api.php/cash.system.getSettings?access_token=' + process.env.VUE_APP_API_TOKEN)
  .then(response => response.text())
  .then(res => {
    process.env.VUE_APP_SETTINGS = res
  })

module.exports = {
  productionSourceMap: false,
  filenameHashing: false,
  devServer: {
    proxy: process.env.VUE_APP_DEV_PROXY
  },
  css: {
    loaderOptions: {
      css: {
        url: false
      }
    }
  },
  configureWebpack: {
    plugins: [
      new webpack.ContextReplacementPlugin(/moment[/\\]locale$/, /en|ru/)
    ],
    externals: function (context, request, callback) {
      if (/xlsx|canvg|pdfmake/.test(request)) {
        return callback(null, 'commonjs ' + request)
      }
      callback()
    }
  },
  chainWebpack: config => {
    config.optimization.delete('splitChunks')
    config.plugins.delete('preload')
    config.plugins.delete('prefetch')
  }
}
