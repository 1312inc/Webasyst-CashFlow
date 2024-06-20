export default {
  methods: {
    $_rowModificatorMixin_rowModificator_category (obj) {
      let string = '<div class="wide flexbox space-8 middle">'
      if (obj.color) {
        string += `<div class="icon custom-m-0 ${
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
        string += '</div>'
      }
      string += `<div title="${obj.name}" class="text-ellipsis">${obj.name}</div>`
      string += '</div>'
      return string
    },

    $_rowModificatorMixin_rowModificator_account (obj) {
      const name = `${obj.name}${obj.currency ? `&nbsp;(${this.$helper.currencySignByCode(obj.currency)})` : ''}`
      return `<div title="${name}" class="text-ellipsis">${name}</div>`
    }
  }
}
