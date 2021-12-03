<template>
  <li :class="classes" :data-itemid="category.id">
    <router-link :to="`/category/${category.id}`" class="flexbox middle">
      <span class="icon"
        ><i class="rounded" :style="`background-color:${category.color};`"></i
      ></span>
      <span>{{ category.name }}</span>
      <span class="count small" v-if="category.is_profit" :title="$t('profit')"
        ><i class="fas fa-coins"></i
      ></span>
    </router-link>
    <SortableList
      :items="childrens"
      :parentId="category.id"
      sortingTarget="category"
      :group="{
        name: $parent.$attrs.group.name,
        put: () => allowChildren ? $parent.$attrs.group.name : false
      }"
    >
      <SortableItemCategory
        v-for="category in childrens"
        :key="category.id"
        :category="category"
        :allowChildren="false"
      />
    </SortableList>
  </li>
</template>

<script>
import SortableList from './SortableList'
export default {
  name: 'SortableItemCategory',
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

  components: {
    SortableList
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
