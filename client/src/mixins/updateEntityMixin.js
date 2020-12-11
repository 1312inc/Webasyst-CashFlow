export default {
  computed: {
    isModeUpdate () {
      return !!this.editedItem
    }
  },

  created () {
    if (this.editedItem) {
      this.model = { ...this.model, ...this.editedItem }
    }
  },

  methods: {
    submit (entity) {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        this.$store
          .dispatch(`${entity}/update`, this.model)
          .then(() => {
            this.$noty.success('Успешно обновлено')
            this.close()
          })
          .catch(() => {
            this.$noty.error('Oops, something went wrong!')
          })
      }
    },

    remove (entity) {
      if (confirm(this.$t('deleteWarning', { type: this.$t('categories') }))) {
        this.$store
          .dispatch(`${entity}/delete`, this.model.id)
          .then(() => {
            this.$noty.success('Успешно удалено')
            this.close()
          })
          .catch((e) => {
            this.$noty.error('Oops, something went wrong!')
          })
      }
    },

    close () {
      this.$parent.$emit('close')
    }
  }
}
