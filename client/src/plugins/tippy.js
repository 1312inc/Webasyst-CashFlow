import { waitForTippy } from '@/utils/waiters'

export default {
  install (Vue) {
    Vue.directive('wa-tippy', {
      async inserted (el, binding) {
        const tippy = await waitForTippy()
        if (!tippy) return
        Vue.nextTick(() => {
          if (binding.value) {
            tippy(el, {
              content: binding.value,
              arrow: false,
              zIndex: 99999
            })
          }
        })
      },
      unbind (el) {
        if (el._tippy) el._tippy.destroy()
      }
    })
  }
}
