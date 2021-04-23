<template>
  <draggable
    :list="categories"
    :disabled="!$permissions.isAdmin"
    :force-fallback="true"
    :delay="1000"
    :delayOnTouchOnly="true"
    @update="sortCategories()"
    tag="ul"
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
