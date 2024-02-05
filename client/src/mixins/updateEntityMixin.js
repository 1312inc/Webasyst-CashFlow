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
            this.close({ action: 'afterSubmit', entity })
          })
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
            this.close({ action: 'afterDelete', entity })
          })
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
