import Vue from 'vue'
import VueRouter from 'vue-router'
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

export default router
