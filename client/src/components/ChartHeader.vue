<template>
  <div class="custom-px-24 custom-py-16 custom-ml-4 custom-p-8-mobile custom-mb-12-mobile">
    <div class="c-header flexbox">
      <div class="wide">
        <slot
          v-if="$helper.isDesktopEnv"
          name="title"
        />
      </div>
      <div>
        <slot name="controls" />
      </div>
    </div>
  </div>
</template>

<script setup>

</script>

<style>
.c-header {
  flex-wrap: wrap;
  gap: 1rem;
}
.c-header:empty {
  display: none;
}
</style>
