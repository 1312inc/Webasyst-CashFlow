<template>
  <draggable
    tag="ul"
    :list="categories"
    @update="sortCategories()"
    class="menu"
  >
    <slot></slot>
  </draggable>
</template>

<script>
import draggable from 'vuedraggable'
export default {
  props: ['categories'],

  components: {
    draggable
  },

  methods: {
    sortCategories () {
      const ids = this.categories.map(e => e.id)
      this.$store.dispatch('category/sort', {
        order: ids
      })
    }
  }
}
</script>
