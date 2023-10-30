<template>
  <div class="c-header custom-m-12 custom-ml-32 custom-ml-12-mobile">
    <slot
      v-if="$helper.isDesktopEnv"
      name="title"
    />

    <slot name="controls" />
  </div>
</template>

<script setup></script>

<style>
.c-header {
  position: relative;
}
</style>
