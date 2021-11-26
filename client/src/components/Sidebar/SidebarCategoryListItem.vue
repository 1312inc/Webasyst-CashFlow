<template>
  <li :class="classes">
    <router-link :to="`/category/${category.id}`" class="flexbox middle">
      <span class="icon"
        ><i class="rounded" :style="`background-color:${category.color};`"></i
      ></span>
      <span>{{ category.name }}</span>
      <span class="count small" v-if="category.is_profit" :title="$t('profit')"><i class="fas fa-coins"></i></span>
    </router-link>
  </li>
</template>

<script>
export default {
  props: ['category'],

  computed: {
    isActive () {
      return this.$store.state.currentType === 'category' && this.$store.state.currentTypeId === this.category.id
    },

    classes () {
      return {
        selected: this.isActive,
        'custom-pl-12': this.category.parent_category_id
      }
    }
  }
}
</script>
