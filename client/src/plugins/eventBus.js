import mitt from 'mitt'

const eventBus = mitt()

export { eventBus as emitter }

export default {
  install (Vue) {
    Vue.prototype.$eventBus = eventBus
  }
}
