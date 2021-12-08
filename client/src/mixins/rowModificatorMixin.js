export default {
  methods: {
    $_rowModificatorMixin_rowModificator_category (obj) {
      return `${obj.color ? `<span class='icon ${
        obj.parent_category_id ? 'custom-ml-12' : ''
      }'><i class='rounded' style='background-color:${
        obj.color
      };'></i></span>` : ''}<span>${obj.name}</span>`
    },

    $_rowModificatorMixin_rowModificator_account (obj) {
      return `${obj.name}${obj.currency ? `&nbsp;(${this.$helper.currencySignByCode(
        obj.currency
      )})` : ''}`
    }
  }
}
