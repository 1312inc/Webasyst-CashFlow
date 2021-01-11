import Vue from 'vue'

export default new Vue({
  data () {
    return {
      multiSelect: false
    }
  },

  methods: {
    multiSelectEnabled (val) {
      this.multiSelect = val
    }
  }
})
