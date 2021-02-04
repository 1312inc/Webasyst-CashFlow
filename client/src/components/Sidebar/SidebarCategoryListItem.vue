<template>
  <li :class="{'selected': isActive}">
    <router-link :to="`/category/${category.id}`" class="flexbox middle">
      <span class="icon"
        ><i class="rounded" :style="`background-color:${category.color};`"></i
      ></span>
      <span>{{ category.name }}</span>
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
