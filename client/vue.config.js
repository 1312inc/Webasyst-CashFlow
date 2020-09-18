module.exports = {
  publicPath: '/webasyst/cash/',
  productionSourceMap: false,
  filenameHashing: false,
  devServer: {
    proxy: 'http://localhost:8888'
  },
  chainWebpack: config => {
    config
      .plugin('prefetch')
      .tap(args => {
        return [
          {
            rel: 'prefetch',
            include: 'asyncChunks',
            fileBlacklist: [
              /\.map$/,
              /pdfmake\.[^.]+\.js$/,
              /xlsx\.[^.]+\.js$/,
              /fabric[^.]*\.[^.]+\.js$/,
              /responsivedefaults\.[^.]+\.js$/
            ]
          }
        ]
      })
  }
}
