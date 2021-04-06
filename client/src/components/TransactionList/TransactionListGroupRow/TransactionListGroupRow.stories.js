import Vuex from 'vuex'
import { i18n } from '@/plugins/locale'
import TransactionListGroupRow from './TransactionListGroupRow'

export default {
  title: 'Cash/TransactionListItem',
  component: TransactionListGroupRow,
  args: {
    showChecker: false
  }
}

const Template = (args, { argTypes }) => ({
  components: { TransactionListGroupRow },
  props: Object.keys(argTypes),
  template: `
    <ul class="c-list list">
      <TransactionListGroupRow :transaction="transaction" :showChecker="showChecker" />
    </ul>
    `,
  store: new Vuex.Store({
    modules: {
      transaction: {
        namespaced: true,
        state: () => ({
          updatedTransactions: []
        })
      },
      account: {
        namespaced: true,
        state: () => ({
          accounts: [args.account]
        }),
        getters: {
          getById: state => id => {
            return state.accounts.find(account => account.id === id)
          }
        }
      },
      category: {
        namespaced: true,
        state: () => ({
          categories: [args.category]
        }),
        getters: {
          getById: state => id => {
            return state.categories.find(category => category.id === id)
          }
        }
      },
      transactionBulk: {
        namespaced: true,
        getters: {
          isSelected: state => id => {
            return true
          }
        }
      }

    }
  }),
  i18n
})

export const LongData = Template.bind({})
LongData.args = {
  category: { id: 17, name: 'long category name long category name', type: 'expense', color: '#880E4F', sort: 0, create_datetime: '2020-12-24 16:09:36', update_datetime: '2021-02-04 13:59:22', is_profit: false },
  account: { id: 61, name: 'long account name long category name', description: 'description', icon: '', currency: 'USD', customer_contact_id: 1, is_archived: false, sort: 0, create_datetime: '2021-03-15 08:42:34', update_datetime: null, stat: { income: 0, expense: 0, summary: 0, incomeShorten: '0', expenseShorten: '0', summaryShorten: '0' } },
  transaction: {
    id: 1786,
    date: '2021-03-15',
    amount: -1234567890,
    description: 'long description long description long description long description',
    category_id: 17,
    account_id: 61,
    is_onbadge: false,
    repeating_id: 1
  }
}

export const normalData = Template.bind({})
normalData.args = {
  category: { id: 17, name: 'category name', type: 'expense', color: '#880E4F', sort: 0, create_datetime: '2020-12-24 16:09:36', update_datetime: '2021-02-04 13:59:22', is_profit: false },
  account: { id: 61, name: 'account name', description: 'description', icon: '', currency: 'USD', customer_contact_id: 1, is_archived: false, sort: 0, create_datetime: '2021-03-15 08:42:34', update_datetime: null, stat: { income: 0, expense: 0, summary: 0, incomeShorten: '0', expenseShorten: '0', summaryShorten: '0' } },
  transaction: {
    id: 1786,
    date: '2021-03-15',
    amount: -123456,
    description: '',
    category_id: 17,
    account_id: 61,
    is_onbadge: false
  }
}

export const externalSource = Template.bind({})
externalSource.args = {
  category: { id: 17, name: 'category name', type: 'expense', color: '#880E4F', sort: 0, create_datetime: '2020-12-24 16:09:36', update_datetime: '2021-02-04 13:59:22', is_profit: false },
  account: { id: 61, name: 'account name', description: 'description', icon: '', currency: 'USD', customer_contact_id: 1, is_archived: false, sort: 0, create_datetime: '2021-03-15 08:42:34', update_datetime: null, stat: { income: 0, expense: 0, summary: 0, incomeShorten: '0', expenseShorten: '0', summaryShorten: '0' } },
  transaction: {
    id: 1786,
    date: '2021-03-15',
    amount: -123456,
    description: '',
    category_id: 17,
    account_id: 61,
    is_onbadge: false,
    external_source_info: {
      name: 'test name',
      color: 'green',
      glyph: 'fas fa-shopping-cart',
      icon: '',
      url: ''
    }
  }
}
