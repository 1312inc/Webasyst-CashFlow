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

  beforeDestroy () {
    this.close()
  },

  methods: {
    submit (entity) {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        this.$store
          .dispatch(`${entity}/update`, this.model)
          .then(() => {
            this.close()
          })
      }
    },

    remove (entity) {
      if (confirm(this.$t('deleteWarning', { type: this.$t('categories') }))) {
        this.$store
          .dispatch(`${entity}/delete`, this.model.id)
          .then(() => {
            this.close()
            this.$router.push({ name: 'Home' })
          })
      }
    },

    close () {
      this.$parent.$emit('close')
    }
  }
}
