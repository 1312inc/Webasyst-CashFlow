<template>
  <div class="c-header custom-m-12 custom-ml-32 custom-ml-12-mobile">
    <slot
      v-if="$helper.isDesktopEnv"
      name="title"
    />
    <div style="position: absolute; top: 0; right: 0;z-index: 9999;">
      <slot name="controls" />
    </div>
  </div>
</template>

<script setup></script>

<style>
.c-header {
  position: relative;
}
</style>
