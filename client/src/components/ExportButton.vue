<template>
    <a v-if="show" :href="link" class="button white"><i class="fas fa-file-download"></i> Export to CSV</a>
</template>

<script>
export default {
  computed: {
    entity () {
      return this.$store.state.transaction.queryParams.filter.split('/')
    },

    show () {
      const allow = ['account', 'category', 'import']
      return this.entity.length === 2 && allow.includes(this.entity[0])
    },

    link () {
      return encodeURI(`${this.$helper.baseUrl}?module=export&action=csv&settings[start_date]=${this.$store.state.transaction.queryParams.from}&settings[end_date]=${this.$store.state.transaction.queryParams.to}&settings[entity_type]=${this.entity[0]}&settings[entity_id]=${this.entity[1]}&type=completed`)
    }
  }
}
</script>
