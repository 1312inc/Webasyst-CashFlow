module.exports = {
  publicPath: '/webasyst/cash/',
  productionSourceMap: false,
  filenameHashing: false,
  devServer: {
    proxy: 'http://localhost:8888'
  }
}
