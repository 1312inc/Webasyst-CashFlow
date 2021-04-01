import updateHeaderTodayCounter from '../utils/updateHeaderTodayCounter'

export default (store) => {
  // listen to mutations
  store.subscribe(({ type, payload }, state) => {
    switch (type) {
      // transaction state update
      case 'transaction/updateTransactions':
      case 'transaction/deleteTransaction':
      case 'transaction/createTransactions':
        return Promise.all([
          store.dispatch('transaction/getTodayCount'),
          store.dispatch('account/getList'),
          store.dispatch('balanceFlow/getBalanceFlow')
        ])
      case 'transaction/updateTransactionProps':
        if ('is_onbadge' in payload.props) {
          store.dispatch('transaction/getTodayCount')
        }
        break
      case 'transaction/setDetailsInterval':
        store.commit('transactionBulk/emptySelectedTransactionsIds')
        break
      case 'transaction/setTodayCount':
        updateHeaderTodayCounter(payload)
    }
  })

  store.subscribeAction({
    after: ({ type, payload }, state) => {
      switch (type) {
        // delete account
        case 'account/delete':
          return Promise.all([
            store.dispatch('transaction/getTodayCount'),
            store.dispatch('balanceFlow/getBalanceFlow')
          ])
        // create account
        case 'account/update':
          if (!payload.id) {
            store.dispatch('balanceFlow/getBalanceFlow')
          }
      }
    }
  })
}
