var webpack = require('webpack')

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
