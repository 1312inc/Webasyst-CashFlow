export default (store) => {
  window.emitter.on('env:createTransactions', (transactions) => {
    store.commit('transaction/createTransactions', transactions)
  })
}
