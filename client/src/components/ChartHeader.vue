<template>
  <div class="custom-p-24">
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
import BlankBox from '../components/BlankBox.vue'
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
