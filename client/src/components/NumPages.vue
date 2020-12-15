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
import Paginate from 'vuejs-paginate'
export default {
  props: {
    total: {
      type: Number
    },

    limit: {
      type: Number
    },

    offset: {
      type: Number
    }
  },

  components: {
    Paginate
  },

  computed: {
    currentPage: {
      get () {
        return Math.ceil(this.offset / this.total) + 1
      },
      set () {
        return false
      }
    },

    pagesCount () {
      return Math.ceil(this.total / this.limit)
    }
  },

  methods: {
    clickCallback (pageNum) {
      this.$emit('changePage', (pageNum - 1) * this.limit)
    }
  }
}
</script>
