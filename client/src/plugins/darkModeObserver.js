import Vue from 'vue'

const root = document.documentElement

const initialDarkMode = root.getAttribute('data-theme') === 'dark'

const darkModeObserver = Vue.observable({
  darkMode: initialDarkMode
})

new MutationObserver(() => {
  darkModeObserver.darkMode = root.getAttribute('data-theme') === 'dark'
}).observe(root, { attributes: true, attributeFilter: ['data-theme'] })

export { initialDarkMode, darkModeObserver }

export default {
  install: Vue => {
    Vue.prototype.$darkModeObserver = darkModeObserver
  }
}
