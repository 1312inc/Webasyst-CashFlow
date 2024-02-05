import mitt from 'mitt'

const eventBus = mitt()

export { eventBus as emitter }

if (window.emitter) {
  window.emitter.on('*', (type, arg) => {
    console.log('%c Emitter ', 'background: #dff758; color: #000000')
    console.log('Type:', type)
    console.log('Argument:', arg)
    console.log('-------------------')
  })
}

export default {
  install (Vue) {
    Vue.prototype.$eventBus = eventBus
  }
}
