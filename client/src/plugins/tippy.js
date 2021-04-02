
export default {
  install (Vue) {
    Vue.directive('wa-tippy', {
      inserted (el, binding, vnode) {
        const tippy = window.tippy || function () { }
        Vue.nextTick(() => {
          if (binding.value) {
            tippy(el, {
              content: binding.value,
              arrow: false
            })
          }
        })
      }
      //   unbind (el) {
      //     console.log(el)
      //     el._tippy && el._tippy.destroy()
      //   },
      //   componentUpdated (el, binding, vnode) {
      //     if (el._tippy) {
      //       const opts = binding.value || {}

      //       if (el.getAttribute('title') && !opts.content) {
      //         opts.content = el.getAttribute('title')
      //         el.removeAttribute('title')
      //       }

      //       if (el.getAttribute('content') && !opts.content) {
      //         opts.content = el.getAttribute('content')
      //       }

      //       el._tippy.set(opts)
      //     }
      //   }

    })
  }
}
