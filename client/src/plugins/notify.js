import Vue from 'vue'
import NotifyComponent from '../components/Notify'

const Component = Vue.extend(NotifyComponent)
const Notify = new Component().$mount()
document.querySelector('body').appendChild(Notify.$el)

export default {
  install: function (Vue) {
    Vue.prototype.$notify = Notify
  }
}
