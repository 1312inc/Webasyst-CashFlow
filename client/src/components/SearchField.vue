<template>
  <div class="custom-p-12">
    <form @submit.prevent="submit" class="input-with-inner-icon left width-100">
      <input
        type="search"
        name="query"
        v-model.trim="queryText"
        :placeholder="$t('search')"
        class="width-100"
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

  methods: {
    submit () {
      if (this.queryText) {
        this.$router.push({ name: 'Search', query: { text: this.queryText } })
      }
    }
  }
}
</script>
