import Vue from 'vue'
import Component from './Component'

const ErrorsComponent = Vue.extend(Component)
const Errors = new ErrorsComponent().$mount()
document.querySelector('body').appendChild(Errors.$el)

export default {
  install: function (Vue) {
    Vue.prototype.$errors = Errors
  }
}
