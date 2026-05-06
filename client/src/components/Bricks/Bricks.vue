<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="bricks custom-mt-0">
    <BrickTransactions />
    <BrickCalendar />
    <router-link
      to="/budget"
      class="brick custom-pt-8"
      :class="{
        'selected': $route.name === 'Plan'
      }"
    >
      <div class="flexbox middle space-8">
        <span class="icon"><i class="fas fa-tachometer-alt text-green" /></span>
        <span>{{ $t("plan") }}</span>
      </div>
    </router-link>
    <a
      v-if="$permissions.canSeeReport"
      :href="`${$helper.baseUrl}report/dds/`"
      class="brick custom-pt-8"
      :class="{
        'selected': $route.path.includes('/report/')
      }"
    >
      <div class="flexbox middle space-8">
        <span class="icon"><i class="fas fa-chart-pie text-pink" /></span>
        <span>{{ $t("reports") }}</span>
      </div>
    </a>
  </div>
</template>

<script setup>
import BrickCalendar from './BrickCalendar'
import BrickTransactions from './BrickTransactions'
</script>
