<template>
  <ul class="c-list list">
    <li
      v-for="i in lines"
      :key="i"
      class="c-item item"
    >
      <div class="skeleton custom-px-16-mobile">
        <div class="flexbox space-12 custom-py-12">
          <div
            v-if="$helper.showMultiSelect()"
            style="min-width: 1rem"
          />
          <div>
            <span class="skeleton-custom-circle size-48" />
          </div>
          <div class="wide">
            <span class="skeleton-line custom-mb-4" />
            <span class="skeleton-line custom-mb-4" />
            <span class="skeleton-line custom-mb-0" />
          </div>
          <div class="width-30">
            <span class="skeleton-line custom-mb-4" />
            <span class="skeleton-line custom-mb-0" />
          </div>
        </div>
      </div>
    </li>
  </ul>
</template>

<script>
export default {
  props: {
    lines: {
      type: Number,
      default: 10
    }
  }
}
</script>
