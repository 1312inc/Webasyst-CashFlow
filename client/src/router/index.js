import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import History from '../views/History.vue'
import Transactions from '../views/Transactions.vue'
import Upnext from '../views/Upnext.vue'
import Search from '../views/Search.vue'
import Import from '../views/Import.vue'
import NotFound from '../views/NotFound.vue'
import { i18n } from '../plugins/locale'

Vue.use(VueRouter)

// TODO: make settings file
const accountName = window.appState?.accountName || ''

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
    component: History,
    meta: {
      title: `${i18n.t('history')} — ${accountName}`
    }
  },
  {
    path: '/transactions',
    name: 'Transactions',
    component: Transactions,
    meta: {
      title: `${i18n.t('transactions')} — ${accountName}`
    }
  },
  {
    path: '/upnext',
    name: 'Upnext',
    component: Upnext,
    meta: {
      title: `${i18n.t('upnext')} — ${accountName}`
    }
  },
  {
    path: '/search',
    name: 'Search',
    component: Search,
    meta: {
      title: `${i18n.t('search.label')} — ${accountName}`
    }
  },
  // TODO: make 404 guard for imports
  {
    path: '/import/:id',
    name: 'Import',
    component: Import,
    meta: {
      title: `${i18n.t('importResults')} — ${accountName}`
    }
  },
  {
    path: '/report/dds'
  },
  {
    path: '/import'
  },
  {
    path: '/shop/settings'
  },
  {
    path: '/plugins'
  },
  {
    path: '/404',
    name: 'NotFound',
    component: NotFound,
    meta: {
      title: `${i18n.t('404.title')} — ${accountName}`
    }
  },
  { path: '*', redirect: { name: 'NotFound' } }
]

const router = new VueRouter({
  mode: process.env.VUE_APP_MODE === 'desktop' ? 'history' : 'hash',
  base: window?.appState?.baseUrl || '/',
  routes,
  scrollBehavior (to, from, savedPosition) {
    return { x: 0, y: 0 }
  }
})

router.beforeEach((to, from, next) => {
  if (to.meta.title) {
    document.title = to.meta.title
  }
  next()
})

export default router
