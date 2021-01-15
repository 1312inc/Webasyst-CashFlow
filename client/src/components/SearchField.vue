<template>
  <div class="custom-p-12">
    <form @submit.prevent="submit" class="state-with-inner-icon left width-100">
      <input
        type="search"
        name="query"
        v-model.trim="queryText"
        :placeholder="$t('search.label')"
        class="width-100 solid"
      />
      <button class="icon"><i class="fas fa-search"></i></button>
    </form>
  </div>
</template>

<script>
export default {
  data () {
    return {
      queryText: ''
    }
  },

  watch: {
    '$route' (val) {
      if (val.name !== 'Search') {
        this.queryText = ''
      }
    }
  },

  methods: {
    submit () {
      if (this.queryText && this.queryText !== this.$route.query.text) {
        this.$router.push({ name: 'Search', query: { text: this.queryText } })
      }
    }
  }
}
</script>
