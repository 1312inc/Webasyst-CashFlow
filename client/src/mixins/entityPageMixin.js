export default {
  created () {
    if (this.$_entityPage_entity) {
      if (this.$route.name === 'Order') {
        if (!this.isModeUpdate) {
          this.model.external = {
            source: this.$_entityPage_entity.app,
            id: this.$_entityPage_entity.entity_id
          }
        }
      }

      if (this.$route.name === 'Contact') {
        if (!this.isModeUpdate) {
          this.model.contractor_contact = {
            name: this.$_entityPage_entity.entity_name,
            userpic: this.$_entityPage_entity.entity_icon
          }
          this.model.contractor_contact_id = this.$_entityPage_entity.entity_id
        }
      }
    }
  },

  computed: {
    $_entityPage_entity () {
      return this.$store.state.entity.entity
    }
  }
}
