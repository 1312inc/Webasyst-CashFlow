export default {
  install (Vue) {
    Vue.directive('wa-tippy', {
      inserted (el, binding, vnode) {
        // define global tippy object from the WA
        window.tippy &&
          Vue.nextTick(() => {
            if (binding.value) {
              window.tippy(el, {
                content: binding.value,
                arrow: false,
                zIndex: 99999
              })
            }
          })
      },
      unbind (el) {
        el._tippy && el._tippy.destroy()
      }
    })
  }
}
