<template>
  <div class="custom-p-16">
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
