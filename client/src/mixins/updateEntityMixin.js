export default {

  data () {
    return {
      controlsDisabled: false
    }
  },

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
        this.controlsDisabled = true
        this.$store
          .dispatch(`${entity}/update`, this.model)
          .then(() => {
            // FIX: Reload page in case of changing type of category / #106.306
            if (this.isModeUpdate && entity === 'category' && this.model.type !== this.editedItem.type) {
              location.reload()
              return
            }
            this.close({ action: 'afterSubmit', entity: { type: entity, name: this.model.name } })
          })
          .catch(() => {})
          .finally(() => {
            this.controlsDisabled = false
          })
      }
    },

    remove (entity) {
      if (confirm(this.$t('deleteWarning', { type: this.$t('categories') }))) {
        this.controlsDisabled = true
        this.$store
          .dispatch(`${entity}/delete`, this.model.id)
          .then(() => {
            this.close({ action: 'afterDelete', entity: { type: entity, name: this.model.name } })
          })
          .catch(() => {})
          .finally(() => {
            this.controlsDisabled = false
          })
      }
    },

    close (message) {
      this.$parent.$emit('close', message)
    }
  }
}
