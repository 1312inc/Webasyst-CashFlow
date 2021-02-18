import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import History from '../views/History.vue'
import Upnext from '../views/Upnext.vue'
import Reports from '../views/Reports.vue'
import Search from '../views/Search.vue'
import Import from '../views/Import.vue'

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
    path: '/history',
    name: 'History',
    component: History
  },
  {
    path: '/upnext',
    name: 'Upnext',
    component: Upnext
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
  },
  {
    path: '/import/:id',
    name: 'Import',
    component: Import
  }
]

const router = new VueRouter({
  mode: process.env.VUE_APP_MODE === 'desktop' ? 'history' : 'hash',
  base: window?.appState?.baseUrl || '/',
  routes,
  scrollBehavior (to, from, savedPosition) {
    return { x: 0, y: 0 }
  }
})

export default router
