export default {
  install (Vue) {
    Vue.directive('wa-tippy', {
      inserted (el, binding, vnode) {
        // define global tippy object from the WA
        const tippy = window.tippy || function () { }
        Vue.nextTick(() => {
          if (binding.value) {
            tippy(el, {
              content: binding.value,
              arrow: false
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
