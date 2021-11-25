import Vue from 'vue'

const initialDarkMode =
  document.documentElement.getAttribute('data-theme') === 'dark'

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
      const targetNode = document.documentElement
      if (targetNode) {
        const config = { attributes: true }
        const callback = mutationsList => {
          for (const mutation of mutationsList) {
            if (mutation.attributeName === 'data-theme') {
              this.darkMode = targetNode.getAttribute('data-theme') === 'dark'
            }
          }
        }
        this.darkModeObserver = new MutationObserver(callback)
        this.darkModeObserver.observe(targetNode, config)
      }
    }
  }
})

export { initialDarkMode }

export default {
  install: Vue => {
    Vue.prototype.$darkModeObserver = DarkModeObserver
  }
}
