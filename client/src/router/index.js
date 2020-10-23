import Vue from 'vue'
import VueRouter from 'vue-router'
import store from '../store'
import Home from '../views/Home.vue'

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
  }
]

const router = new VueRouter({
  mode: 'history',
  base: window?.appState?.baseUrl || '/',
  routes
})

router.beforeEach((to, from, next) => {
  if (to.name === 'Account' || to.name === 'Category') {
    // if no Category
    if (to.params.id < 0) {
      next({ name: 'Home' })
    } else {
      store.dispatch('updateCurrentType', {
        name: to.name.toLowerCase(),
        id: +to.params.id
      })
      next()
    }
  } else {
    store.dispatch('updateCurrentType', { name: '', id: null })
    next()
  }
})

export default router
