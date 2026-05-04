import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import History from '../views/History.vue'
import Transactions from '../views/Transactions.vue'
import Date from '../views/Date.vue'
import Upnext from '../views/Upnext.vue'
import Search from '../views/Search.vue'
import Import from '../views/Import.vue'
import Trash from '../views/Trash.vue'
import Entity from '../views/Entity.vue'
import NotFound from '../views/NotFound.vue'
import Calendar from '../views/Calendar.vue'
import FormAdd from '../views/FormAdd.vue'
import { permissions } from '../plugins/permissions'
import { moment } from '@/plugins/numeralMoment.js'

const SSR_MODE_PAGE_URL_ALIASES = ['/report/*', '/import', '/import/new/*', '/shop/settings', '/plugins', '/upgrade', '/automation']

Vue.use(VueRouter)

const baseUrl = window.appState?.baseUrl || '/'

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
    path: '/transactions',
    name: 'Transactions',
    component: Transactions
  },
  {
    path: '/calendar',
    name: 'Calendar',
    component: Calendar
  },
  {
    path: '/upnext',
    name: 'Upnext',
    component: Upnext
  },
  {
    path: '/date/:date',
    name: 'Date',
    component: Date,
    beforeEnter: (to, from, next) => {
      if (moment(to.params.date, 'YYYY-MM-DD', true).isValid()) {
        next()
      } else {
        next({ name: 'NotFound' })
      }
    }
  },
  {
    path: '/search',
    name: 'Search',
    component: Search
  },
  // TODO: make 404 guard for imports
  {
    path: '/import/:id',
    name: 'Import',
    component: Import
  },
  {
    path: '/trash',
    name: 'Trash',
    component: Trash,
    meta: {
      requiresAdminRights: true
    }
  },
  {
    path: '/external/shop/order/:id',
    name: 'Order',
    component: Entity,
    meta: {
      showChart: false,
      getExternalEntitySource: 'shop',
      fetchTransactionsFilter: (id) => `external/shop.${id}`
    }
  },
  {
    path: '/contact/:id',
    name: 'Contact',
    component: Entity,
    meta: {
      showChart: true,
      getExternalEntitySource: 'contacts',
      fetchTransactionsFilter: (id) => `contractor/${id}`
    }
  },
  {
    path: '/form/add/:entity',
    name: 'Form',
    component: FormAdd,
    props: (route) => ({ entity: route.params.entity, type: route.query.type }),
    beforeEnter: (to, from, next) => {
      if (['account', 'category'].includes(to.params.entity)) {
        next()
      } else {
        next({ name: 'NotFound' })
      }
    }
  },
  {
    path: '/plan',
    name: 'Plan',
    component: () => import('../views/Plan.vue')
  },
  {
    path: '/report',
    alias: SSR_MODE_PAGE_URL_ALIASES
  },
  {
    path: '/404',
    name: 'NotFound',
    component: NotFound
  },
  { path: '*', redirect: { name: 'NotFound' } }
]

const router = new VueRouter({
  mode: !window.appState.webView ? 'history' : 'hash',
  base: baseUrl,
  routes,
  scrollBehavior () {
    return { x: 0, y: 0 }
  }
})

router.beforeEach((to, from, next) => {
  console.log(`%c Navigate to ${to.name} `, 'background: #222; color: #bada55')
  console.log('-------------------')

  if (to.matched.some(record => record.meta.requiresAdminRights)) {
    if (!permissions.isAdmin) {
      next({
        name: 'Home'
      })
    }
  }

  next()
})

export default router
