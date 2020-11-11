<template>
  <paginate
    v-if="pagesCount > 1"
    v-model="currentPage"
    :page-count="pagesCount"
    :click-handler="clickCallback"
    :prev-text="'←'"
    :next-text="'→'"
    :container-class="'paging'"
    :active-class="'selected'"
  >
  </paginate>
</template>

<script>
import { mapState } from 'vuex'
import Paginate from 'vuejs-paginate'
export default {
  components: {
    Paginate
  },

  computed: {
    ...mapState('transaction', ['transactions']),

    pagesCount () {
      return Math.ceil(this.transactions.total / this.transactions.limit)
    },

    currentPage: {
      get () {
        return this.transactions.offset / this.transactions.limit + 1
      },
      set () {
        return false
      }
    }
  },

  methods: {
    clickCallback (pageNum) {
      this.$store.commit('transaction/updateQueryParams', {
        offset: (pageNum - 1) * this.transactions.limit
      })
    }
  }
}
</script>
