<template>
    <div>
        <div v-if="searchResult.data.length">
            <div v-for="(item, i) in searchResult.data" :key="i">
                {{ item }}
            </div>
        </div>
        <div v-else>
            {{ $t('noResults') }}
        </div>
    </div>
</template>

<script>
import api from '../plugins/api'
export default {

  data () {
    return {
      searchResult: {
        offset: 0,
        limit: 100,
        total: 0,
        data: []
      }
    }
  },

  created () {
    this.makeRequest()
  },

  methods: {
    async makeRequest (params = {}) {
      const { data } = await api.get('cash.transaction.getList', {
        params: {
          filter: `search/${this.$route.query.text}`,
          offset: params.offset || 0,
          limit: params.limit || 100
        }
      })
      this.searchResult = data
    }
  }

}
</script>
