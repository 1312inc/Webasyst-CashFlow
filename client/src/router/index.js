import Vue from 'vue'
import VueRouter from 'vue-router'
import store from '../store'
import Home from '../views/Home.vue'
import Transactions from '../views/Transactions.vue'
import Reports from '../views/Reports.vue'
import Search from '../views/Search.vue'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/account/:id',
    name: 'Account',
    component: Home
  },
  {
    path: '/category/:id',
    name: 'Category',
    component: Home
  },
  {
    path: '/currency/:id',
    name: 'Currency',
    component: Home
  },
  {
    path: '/transactions',
    name: 'Transactions',
    component: Transactions
  },
  {
    path: '/reports',
    name: 'Reports',
    component: Reports
  },
  {
    path: '/search',
    name: 'Search',
    component: Search
  }
]

const router = new VueRouter({
  mode: process.env.VUE_APP_MODE === 'desktop' ? 'history' : 'hash',
  base: window?.appState?.baseUrl || '/',
  routes
})

router.beforeEach((to, from, next) => {
  if (to.name === 'Account' || to.name === 'Category' || to.name === 'Currency') {
    // if no Category
    if (to.params.id < 0 && to.params.id !== '-1312') {
      next({ name: 'Home' })
    } else {
      store.dispatch('updateCurrentType', {
        name: to.name.toLowerCase(),
        id: +to.params.id || to.params.id
      })
      next()
    }
  } else {
    store.dispatch('updateCurrentType', { name: '', id: null })
    next()
  }
})

export default router
