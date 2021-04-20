<template>
  <div class="flexbox space-4 vertical-mobile small gray">
    <div
      v-if="category.name" class="text-ellipsis"
      :style="`color:${category.color}`"
    >
      {{ category.name }}
    </div>
    <div v-if="account.name" class="text-ellipsis">
      {{ account.name }}
    </div>
  </div>
</template>
<script>
export default {
  props: ['collapseHeaderData', 'category', 'account', 'transaction']
}
</script>
