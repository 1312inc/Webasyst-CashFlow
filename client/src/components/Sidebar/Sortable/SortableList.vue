<template>
  <draggable
    :list="items"
    :disabled="!$permissions.isAdmin"
    :component-data="{attrs: { 'data-parentid': parentId }}"
    :force-fallback="true"
    :delay="1000"
    :delayOnTouchOnly="true"
    @sort="sort"
    @add="add"
    :group="group"
    ghost-class="ghost"
    tag="ul"
    class="menu"
  >
    <slot></slot>
  </draggable>
</template>

<script>
import draggable from 'vuedraggable'
export default {
  props: {
    items: {
      type: Array,
      default: () => []
    },
    sortingTarget: {
      type: String,
      required: true
    },
    group: {
      type: Object
    },
    parentId: {
      type: Number,
      default: null
    }
  },

  components: {
    draggable
  },

  methods: {
    add (e) {
      const data = {
        id: +e.item.dataset.itemid,
        parent_category_id: +e.to.dataset.parentid || null
      }
      this.$store.dispatch(`${this.sortingTarget}/updateParams`, data)
    },

    sort () {
      const ids = this.items.map(e => e.id)
      if (ids.length) {
        this.$store.dispatch(`${this.sortingTarget}/sort`, {
          order: ids
        })
      }
    }
  }
}
</script>
