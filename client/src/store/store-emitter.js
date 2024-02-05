export default (store) => {
  if (window.emitter) {
    window.emitter.on('env:createTransactions', (transactions) => {
      store.commit('transaction/createTransactions', transactions)
    })
  }
}
