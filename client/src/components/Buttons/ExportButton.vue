<template>
  <a
    v-if="showComponent"
    :href="link"
    class="button light-gray outlined"
    target="_blank"
    ><span
      ><i class="fas fa-file-excel" style="color: #499b5e"></i>
      {{ $t("export") }}</span
    ></a
  >
</template>

<script>
export default {
  computed: {
    entity () {
      return this.$store.state.transaction.queryParams.filter.split('/')
    },

    showComponent () {
      const allow = ['account', 'category']
      return this.entity.length === 2 && allow.includes(this.entity[0])
    },

    detailsInterval () {
      return this.$store.state.transaction.detailsInterval
    },

    link () {
      return encodeURI(
        `${this.$helper.baseUrl}?module=export&action=csv&settings[start_date]=${this.detailsInterval.from}&settings[end_date]=${this.detailsInterval.to}&settings[entity_type]=${this.entity[0]}&settings[entity_id]=${this.entity[1]}`
      )
    }
  }
}
</script>
