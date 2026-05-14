<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <div class="bricks custom-mt-0">
    <BrickCalendar />
    <BrickTransactions />
    <a
      v-if="$permissions.canSeeReport"
      :href="`${$helper.baseUrl}report/dds/`"
      class="brick custom-pt-8"
      :class="{
        'selected': $route.path.includes('/report/')
      }"
    >
      <div class="flexbox middle space-8">
        <span class="icon"><i class="fas fa-chart-pie text-green" /></span>
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
        <span class="icon"><i class="fas fa-download text-blue" /></span>
        <span>{{ $t("import") }}</span>
      </div>
    </a>
    <router-link
      to="/budget"
      class="brick custom-pt-8"
      :class="{
        'selected': $route.name === 'Plan'
      }"
    >
      <div class="flexbox middle space-8">
        <span class="icon"><i class="fas fa-tachometer-alt text-purple" /></span>
        <span>{{ $t("plan") }}</span>
      </div>
    </router-link>
    <a
      v-if="$permissions.isAdmin"
      :href="`${$helper.baseUrl}automation/`"
      class="brick custom-pt-8"
      :class="{
        'selected': $route.path === '/automation/'
      }"
    >
      <div class="flexbox middle space-8">
        <span class="icon"><i class="fas fa-robot text-orange" /></span>
        <span>{{ $t("bots") }}</span>
      </div>
    </a>
  </div>
</template>

<script setup>
import BrickCalendar from './BrickCalendar'
import BrickTransactions from './BrickTransactions'
</script>
