export default {
  methods: {
    $_rowModificatorMixin_rowModificator_category (obj) {
      return `<span class='icon ${
        obj.parent_category_id ? 'custom-ml-12' : ''
      }'><i class='rounded' style='background-color:${
        obj.color
      };'></i></span><span>${obj.name}</span>`
    }
  }
}
