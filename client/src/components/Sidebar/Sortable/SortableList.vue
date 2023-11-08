<template>
  <draggable
    :list="items"
    :disabled="!$permissions.isAdmin"
    :component-data="{attrs: { 'data-parentid': parentId }}"
    :force-fallback="true"
    :delay="1000"
    :delay-on-touch-only="true"
    :group="group"
    ghost-class="ghost"
    tag="ul"
    class="menu"
    @sort="sort"
    @add="add"
  >
    <slot />
  </draggable>
</template>

<script>
import draggable from 'vuedraggable'
export default {

  components: {
    draggable
  },
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

  data () {
    return {
      oldOrder: []
    }
  },

  created () {
    this.oldOrder = this.items.map(e => e.id)
  },

  methods: {
    add (e) {
      const data = {
        id: +e.item.dataset.itemid,
        parent_category_id: +e.to.dataset.parentid || null
      }
      this.$store.dispatch(`${this.sortingTarget}/updateParams`, data)
    },

    async sort () {
      const newOrder = this.items.map(e => e.id)
      if (newOrder.length) {
        await this.$store.dispatch(`${this.sortingTarget}/sort`, {
          oldOrder: this.oldOrder,
          newOrder
        })
        this.oldOrder = newOrder
      }
    }
  }
}
</script>
