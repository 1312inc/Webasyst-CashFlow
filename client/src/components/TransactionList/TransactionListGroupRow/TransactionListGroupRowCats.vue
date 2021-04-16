<template>
  <div class="flexbox space-4 vertical-mobile small gray">
    <div v-if="category.name && !collapseHeaderData" class="text-ellipsis">
      {{ category.name }}
    </div>
    <span v-if="!collapseHeaderData" class="desktop-and-tablet-only"> / </span>
    <div v-if="account.name" class="text-ellipsis">
      {{ account.name }}
    </div>
  </div>
</template>
<script>
export default {
  props: ['collapseHeaderData', 'category', 'account']
}
</script>
