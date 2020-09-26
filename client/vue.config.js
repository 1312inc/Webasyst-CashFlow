module.exports = {
  publicPath: '/webasyst/cash/',
  productionSourceMap: false,
  filenameHashing: false,
  devServer: {
    proxy: 'http://localhost:8888'
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

  chainWebpack: config => {
    config.optimization.delete('splitChunks')
  }
}
