<template>
  <li
    :class="classes"
    :data-itemid="category.id"
  >
    <router-link
      :to="`/category/${category.id}`"
      class="flexbox middle"
    >
      <span
        v-if="category.glyph"
        :key="category.color"
        class="icon"
      >
        <i
          :class="category.glyph"
          :style="`color:${category.color};`"
        />
      </span>
      <span
        v-else
        class="icon"
      >
        <i
          class="rounded"
          :style="`background-color:${category.color};`"
        />
      </span>
      <span>{{ category.name }}</span>
      <span
        v-if="category.is_profit"
        class="count small"
        :title="$t('profit')"
      ><i class="fas fa-coins" /></span>
    </router-link>
    <SortableList
      :items="childrens"
      :parent-id="category.id"
      sorting-target="category"
      :group="{
        name: $parent.$attrs.group.name,
        put: () => (allowChildren ? $parent.$attrs.group.name : false)
      }"
    >
      <SortableItemCategory
        v-for="category in childrens"
        :key="category.id"
        :category="category"
        :allow-children="false"
      />
    </SortableList>
  </li>
</template>

<script>
import SortableList from './SortableList'
export default {
  name: 'SortableItemCategory',

  components: {
    SortableList
  },
  props: {
    category: {
      type: Object,
      required: true
    },
    allowChildren: {
      type: Boolean,
      default: true
    }
  },

  computed: {
    classes () {
      return {
        selected:
          this.$store.state.currentType === 'category' &&
          this.$store.state.currentTypeId === this.category.id
      }
    },

    childrens () {
      return this.$store.getters['category/getChildren'](this.category.id)
    }
  }
}
</script>
