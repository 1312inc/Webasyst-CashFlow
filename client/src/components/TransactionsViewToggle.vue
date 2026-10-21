<template>
  <div
    v-if="$appState.isDesktop && !$isSpaMobileMode"
    class="toggle c-transactions-view-toggle"
  >
    <span
      :class="{ selected: transactionsViewMode === 'table' }"
      :title="$t('transactionsViewTable')"
      @click="transactionsViewMode = 'table'"
    >
      <i class="fas fa-table" />
    </span>
    <span
      :class="{ selected: transactionsViewMode === 'list' }"
      :title="$t('transactionsViewList')"
      @click="transactionsViewMode = 'list'"
    >
      <i class="fas fa-list" />
    </span>
  </div>
</template>

<script setup>
import { transactionsViewMode } from '@/utils/transactionsView'
</script>

<style scoped>
.toggle {
  align-self: start;
}
</style>
