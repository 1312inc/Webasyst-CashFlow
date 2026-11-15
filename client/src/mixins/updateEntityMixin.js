import { appStateService } from '@/services/appState'
import { confirmDeletion } from '@/services/confirmDeletion'

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
      for (const prop in this.model) {
        if (prop in this.editedItem) {
          if (prop === 'is_imaginary' && this.editedItem.is_imaginary === 0) {
            if (this.editedItem.accountable_contact_id) {
              this.model[prop] = '-2'
            } else {
              this.model[prop] = '0'
            }
          } else { this.model[prop] = this.editedItem[prop] }
        }
      }
    }
  },

  methods: {
    submit (entity) {
      if (this.controlsDisabled) return
      this.$v.$touch()
      if (!this.$v.$invalid) {
        this.controlsDisabled = true

        const params = {
          ...this.model
        }

        if (params.is_imaginary === '-2') {
          params.is_imaginary = '0'
        }

        this.$store
          .dispatch(`${entity}/update`, params)
          .then(result => {
            if (result === false) return
            // The host reloads its catalog after a WebView dialog closes.
            if (!appStateService.nativeDialog && this.isModeUpdate && entity === 'category' && this.model.type !== this.editedItem.type) {
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

    async remove (entity) {
      if (this.controlsDisabled) return
      const { id, name } = this.model
      this.controlsDisabled = true
      try {
        if (!await confirmDeletion(`deleteWarning.${entity}`) || this._isDestroyed || this.model.id !== id) return
        const result = await this.$store.dispatch(`${entity}/delete`, id)
        if (result !== false) this.close({ action: 'afterDelete', entity: { type: entity, name } })
      } catch (_) {
        // The API client displays the error.
      } finally {
        this.controlsDisabled = false
      }
    },

    close (message) {
      this.$parent.$emit('close', message)
    }
  }
}
