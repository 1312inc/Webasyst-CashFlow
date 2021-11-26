export default {
  methods: {
    $_rowModificatorMixin_rowModificator_category (obj) {
      const icon = obj.parent_category_id
        ? "<span class='icon custom-ml-12'></span>"
        : `<span class='icon'><i class='rounded' style='background-color:${obj.color};'></i></span>`
      return `${icon}<span>${obj.name}</span>`
    }
  }
}
