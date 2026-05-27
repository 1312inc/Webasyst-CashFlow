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
        <span class="icon"><i class="fas fa-chart-pie text-blue" /></span>
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
        <span class="icon"><i class="fas fa-download text-green" /></span>
        <span>{{ $t("import") }}</span>
      </div>
    </a>
    <router-link
      v-if="$permissions.isAdmin"
      to="/budget"
      class="brick custom-pt-8"
      :class="{
        'selected': $route.name === 'Plan'
      }"
    >
      <div class="flexbox middle space-8">
        <div class="c-icon-with-badge">
          <span class="icon"><i
            class="fas fa-tachometer-alt"
            :class="{'text-red': isPremium, 'text-light-gray': !isPremium}"
          /></span>
          <span
            v-if="!isPremium"
            class="c-icon-badge"
          >
            <i class="fas fa-star text-yellow" />
          </span>
        </div>
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
        <div class="c-icon-with-badge">
          <span class="icon"><i
            class="fas fa-robot"
            :class="{'text-brown': isPremium, 'text-light-gray': !isPremium}"
          /></span>
          <span
            v-if="!isPremium"
            class="c-icon-badge"
          >
            <i class="fas fa-star text-light-gray" />
          </span>
        </div>
        <span>{{ $t("bots") }}</span>
      </div>
    </a>
  </div>
</template>

<script setup>
import BrickCalendar from './BrickCalendar'
import BrickTransactions from './BrickTransactions'
import { computed } from 'vue'

const isPremium = computed(() => window.appState?.isPremium)

</script>

<style>
.c-icon-with-badge {
  position: relative;
  top: -1px;
}

.c-icon-badge {
  position: absolute;
  bottom: 0;
  right: 0;
  transform: translate(50%, 50%);
  background-color: var(--background-color-blank);
  border-radius: 50%;
  padding: 1px;
  width: 9px;
  height: 9px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 7px;
}

</style>
