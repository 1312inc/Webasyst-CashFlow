var webpack = require('webpack')

module.exports = {
  publicPath: '/webasyst/cash/',
  productionSourceMap: false,
  filenameHashing: false,
  devServer: {
    proxy: 'http://localhost:8888'
  },

  configureWebpack: {
    plugins: [
      new webpack.ContextReplacementPlugin(/moment[/\\]locale$/, /en|ru/)
      // new webpack.IgnorePlugin(/^\.\/locale$/, /moment$/)
    ],
    externals: function (context, request, callback) {
      if (/xlsx|canvg|pdfmake/.test(request)) {
        return callback(null, 'commonjs ' + request)
      }
      callback()
    }

  },

  // configureWebpack: {
  //   optimization: {
  //     splitChunks: {
  //       cacheGroups: {
  //         default: false,
  //         common: false,
  //         styles: {
  //           name: 'app',
  //           test: /\.(s?css|vue)$/, // chunks plugin has to be aware of all kind of files able to output css in order to put them together
  //           chunks: 'initial',
  //           minChunks: 1,
  //           enforce: true
  //         }
  //       }
  //     }
  //   }
  // },
  pluginOptions: {
    webpackBundleAnalyzer: {
      openAnalyzer: true
    }
  },

  chainWebpack: config => {
    config.optimization.delete('splitChunks')
  }
}
