/**
 * SPA initial state injected from the backend layout into `window.appState`.
 * Use this module instead of reading `window.appState` directly.
 */

function resolveState () {
  if (typeof window !== 'undefined' && window.appState && typeof window.appState === 'object') {
    return window.appState
  }
  return {}
}

const state = resolveState()

export const appStateService = {
  /** Raw state object from the layout (same reference as `window.appState`). */
  get state () {
    return state
  },

  get (key, fallback = undefined) {
    const value = state[key]
    return value === undefined || value === null ? fallback : value
  },

  get webView () {
    return !!state.webView
  },

  get isDesktop () {
    return !state.webView
  },

  get isPremium () {
    return !!state.isPremium
  },

  get accountName () {
    return state.accountName || ''
  },

  get baseUrl () {
    return state.baseUrl || '/'
  },

  get baseApiUrl () {
    return state.baseApiUrl || ''
  },

  get baseStaticUrl () {
    return state.baseStaticUrl || ''
  },

  get token () {
    return state.token || ''
  },

  get lang () {
    return state.lang || 'en_US'
  },

  get accounts () {
    return state.accounts || []
  },

  get categories () {
    return state.categories || []
  },

  get currencies () {
    return state.currencies || []
  },

  get apiSettings () {
    return state.api_settings || {}
  },

  get rights () {
    return state.api_settings?.rights || {}
  },

  get shopscriptInstalled () {
    return !!state.shopscriptInstalled
  },

  get emptyFlow () {
    return !!state.emptyFlow
  },

  /** Page title suffix: ` – Account name` (vue-meta `titleTemplate`). */
  get titleTemplate () {
    return this.accountName ? `%s – ${this.accountName}` : '%s'
  },

  formatTitle (title) {
    return this.accountName ? `${title} – ${this.accountName}` : title
  }
}

/** @deprecated Prefer `appStateService` getters; kept for gradual migration and template bindings. */
export const appState = state

export default {
  install (Vue) {
    Vue.prototype.$appState = appStateService
  }
}
