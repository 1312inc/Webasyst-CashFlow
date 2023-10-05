export default {
  methods: {
    $_rowModificatorMixin_rowModificator_category (obj) {
      let string = ''
      if (obj.color) {
        string = `<span class="icon ${
          obj.parent_category_id ? 'custom-ml-12' : ''
        }">`
        if (obj.glyph) {
          string += `
            <i class="${obj.glyph}" style="color:${obj.color};"></i>
          `
        } else {
          string += `
          <i class="rounded" style="background-color:${obj.color};"></i>
            `
        }
        string += '</span>'
      }
      string += `<span>${obj.name}</span>`
      return string
    },

    $_rowModificatorMixin_rowModificator_account (obj) {
      return `${obj.name}${
        obj.currency
          ? `&nbsp;(${this.$helper.currencySignByCode(obj.currency)})`
          : ''
      }`
    }
  }
}
