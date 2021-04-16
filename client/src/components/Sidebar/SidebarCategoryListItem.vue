<template>
  <li :class="{'selected': isActive}">
    <router-link :to="`/category/${category.id}`" class="flexbox middle">
      <span class="icon"
        ><i class="rounded" :style="`background-color:${category.color};`"></i
      ></span>
      <span>{{ category.name }}</span>
      <span class="count small" v-if="category.is_profit" :title="$t('profit')"><i class="fas fa-arrow-right"></i></span>
    </router-link>
  </li>
</template>

<script>
export default {
  props: ['category'],

  computed: {
    isActive () {
      return this.$store.state.currentType === 'category' && this.$store.state.currentTypeId === this.category.id
    }
  }
}
</script>
