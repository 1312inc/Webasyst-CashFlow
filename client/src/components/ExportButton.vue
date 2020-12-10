<template>
  <a v-if="showComponent" :href="link" target="_blank" class="small"
    ><i class="fas fa-file-download"></i> Export to CSV</a
  >
</template>

<script>
export default {
  props: {
    type: {
      type: String,
      required: true
    }
  },

  computed: {
    entity () {
      return this.$store.state.transaction.queryParams.filter.split('/')
    },

    showComponent () {
      const allow = ['account', 'category', 'import']
      return this.entity.length === 2 && allow.includes(this.entity[0])
    },

    link () {
      return encodeURI(
        `${this.$helper.baseUrl}?module=export&action=csv&settings[start_date]=${this.$store.state.transaction.queryParams.from}&settings[end_date]=${this.$store.state.transaction.queryParams.to}&settings[entity_type]=${this.entity[0]}&settings[entity_id]=${this.entity[1]}&type=${this.type}`
      )
    }
  }
}
</script>
