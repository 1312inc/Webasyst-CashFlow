<template>
  <ul class="c-list list">
    <li
      v-for="i in 10"
      :key="i"
      class="item"
      style="width: 100%; max-width: 400px"
    >
      <div class="skeleton">
        <div class="flexbox space-12 custom-py-12">
          <div v-if="$helper.showMultiSelect()" style="width: 1rem"></div>
          <div>
            <span class="skeleton-custom-circle size-48"></span>
          </div>
          <div class="wide">
            <span class="skeleton-line custom-mb-4"></span>
            <span class="skeleton-line custom-mb-4"></span>
            <span class="skeleton-line custom-mb-0"></span>
          </div>
          <div class="width-30">
            <span class="skeleton-line custom-mb-4"></span>
            <span class="skeleton-line custom-mb-0"></span>
          </div>
        </div>
      </div>
    </li>
  </ul>
</template>
