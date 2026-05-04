<!-- eslint-disable vue/multi-word-component-names -->
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
      :href="`${$helper.baseUrl}plan/`"
      class="brick custom-pt-8"
      :class="{
        'selected': $route.name === 'Plan'
      }"
      @click.prevent="() => { $router.push('/budget') }"
    >
      <div class="flexbox middle space-8">
        <span class="icon"><i class="fas fa-download" /></span>
        <span>{{ $t("plan") }}</span>
      </div>
    </a>
  </div>
</template>

<script setup>
import BrickCalendar from './BrickCalendar'
import BrickTransactions from './BrickTransactions'
</script>
