<template>
  <div class="bricks custom-mt-0">
    <BrickTransactions />
    <BrickCalendar />
    <a
      v-if="$permissions.canSeeReport"
      :href="`${$helper.baseUrl}report/dds/`"
      class="brick custom-pt-8"
      :class="{
        'selected': $route.path.includes('/report/')
      }"
    >
      <div class="flexbox middle space-8">
        <span class="icon"><i class="fas fa-chart-pie" /></span>
        <span>{{ $t("reports") }}</span>
      </div>
    </a>
    <a
      v-if="$permissions.canImport"
      :href="`${$helper.baseUrl}import/`"
      class="brick custom-pt-8"
      :class="{
        'selected': $route.path === '/import/'
      }"
    >
      <div class="flexbox middle space-8">
        <span class="icon"><i class="fas fa-download" /></span>
        <span>{{ $t("import") }}</span>
      </div>
    </a>
  </div>
</template>

<script setup>
import BrickCalendar from './BrickCalendar'
import BrickTransactions from './BrickTransactions'
</script>
