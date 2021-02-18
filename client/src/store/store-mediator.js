export default (store) => {
  // listen to mutations
  store.subscribe(({ type, payload }, state) => {
    switch (type) {
      // transaction state update
      case 'transaction/updateTransactions':
      case 'transaction/deleteTransaction':
      case 'transaction/setCreatedTransactions':
        return Promise.all([
          store.dispatch('transaction/getTodayCount'),
          store.dispatch('account/getList'),
          store.dispatch('balanceFlow/getBalanceFlow')
        ])
      case 'account/update':
        return Promise.all([
          store.dispatch('transaction/getTodayCount'),
          store.dispatch('balanceFlow/getBalanceFlow')
        ])
    }
  })
}
