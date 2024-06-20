import updateHeaderTodayCounter from '../utils/updateHeaderTodayCounter'

const dispatchAll = (store) => {
  return Promise.all([
    store.dispatch('transaction/getTodayCount'),
    store.dispatch('account/getList'),
    store.dispatch('balanceFlow/getBalanceFlow')
  ])
}

export default (store) => {
  // listen to mutations
  store.subscribe(({ type, payload }, state) => {
    switch (type) {
      // transaction state update
      case 'transaction/updateTransactions':
      case 'transaction/deleteTransaction':
      case 'transaction/createTransactions':
        return dispatchAll(store)
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
    before: ({ type, payload }) => {
      // delete Transaction
      if (type === 'transaction/delete') {
        store.commit('transactionBulk/unselect', [payload.id])
      }
    },
    after: ({ type }) => {
      switch (type) {
        // delete account
        case 'category/delete':
        case 'account/delete':
          store.dispatch('transaction/getTodayCount')
          store.dispatch('balanceFlow/getBalanceFlow')
          break
        // create account
        case 'account/update':
          store.dispatch('balanceFlow/getBalanceFlow')
          break
        case 'transactionBulk/bulkMove':
        case 'transactionBulk/restore':
        case 'transactionBulk/purge':
          return Promise.all([
            store.dispatch('transaction/fetchTransactions'),
            dispatchAll(store)
          ])
      }
    }
  })
}
