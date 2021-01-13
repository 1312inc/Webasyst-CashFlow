import utils from '@/mixins/utilsMixin.js'
export default {
  mixins: [utils],

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
          .catch((e) => {
            this.handleApiError(e)
          })
      }
    },

    remove (entity) {
      if (confirm(this.$t('deleteWarning', { type: this.$t('categories') }))) {
        this.$store
          .dispatch(`${entity}/delete`, this.model.id)
          .then(() => {
            if (entity === 'account') {
              this.$router.push({ name: 'Home' })
            } else {
              this.close()
            }
          })
          .catch((e) => {
            this.handleApiError(e)
          })
      }
    },

    close () {
      this.$parent.$emit('close')
    }
  }
}
