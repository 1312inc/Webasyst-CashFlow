import Vue from 'vue'

const initialDarkMode = window.appState?.theme === 'dark' ||
  (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ||
  document.getElementById('wa-dark-mode')?.getAttribute('media') === '(prefers-color-scheme: light)'

const DarkModeObserver = new Vue({
  data () {
    return {
      darkMode: initialDarkMode
    }
  },

  created () {
    this.addDarkModeObserver()
  },

  beforeDestroy () {
    if (this.darkModeObserver) {
      this.darkModeObserver.disconnect()
    }
  },

  methods: {
    addDarkModeObserver () {
      // listen Cash mode switch method
      const targetNode = document.getElementById('wa-dark-mode')
      if (targetNode) {
        const config = { attributes: true }
        const callback = (mutationsList) => {
          for (const mutation of mutationsList) {
            if (mutation.attributeName === 'media') {
              return this.switchMode(targetNode.getAttribute('media'))
            }
          }
        }
        this.darkModeObserver = new MutationObserver(callback)
        this.darkModeObserver.observe(targetNode, config)
      }

      // listen native mode switch
      window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        const newColorScheme = e.matches ? 'dark' : 'light'
        this.switchMode(newColorScheme)
      })
    },

    switchMode (scheme) {
      this.darkMode = scheme === '(prefers-color-scheme: light)' || scheme === 'dark'
    }
  }
})

export { initialDarkMode }

export default {
  install: Vue => {
    Vue.prototype.$darkModeObserver = DarkModeObserver
  }
}
