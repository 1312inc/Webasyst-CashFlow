<template functional>
  <div class="flexbox space-4 vertical-mobile small gray">
    <div
      v-if="props.category.name"
      :style="`color:${props.category.color}`"
      class="text-ellipsis"
    >
      {{ props.category.name }}
    </div>
    <div v-if="props.account.name" class="text-ellipsis">
      {{ props.account.name }}
    </div>
  </div>
</template>
