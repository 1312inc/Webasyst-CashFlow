<script setup>
import { emitter } from '@/plugins/eventBus'
import store from '@/store'
import { computed } from 'vue'
import DropdownWaFloating from '@/components/Inputs/DropdownWaFloating.vue'

const props = defineProps(['transaction'])

const category = computed(() => store.getters['category/getById'](props.transaction.category_id))
const currency = computed(() => store.getters['account/getById'](props.transaction.account_id)?.currency)

function onClick () {
  emitter.emit('openAddTransactionModal', {
    transaction: props.transaction
  })
}
</script>

<template>
  <DropdownWaFloating
    strategy="fixed"
    :hide-on-mobile="true"
  >
    <template #toggler>
      <div
        :style="`color: ${category.color}`"
        class="icg-row align-left nowrap"
        @click.prevent.stop="() => { if (!$helper.isTabletMediaQuery()) { onClick() } }"
      >
        <span
          v-if="category.glyph"
          :key="category.color"
          class="icon baseline"
        >
          <i
            :class="category.glyph"
            :style="`color:${category.color};`"
          />
        </span>
        <span
          v-else
          class="icon size-12 baseline"
        >
          <i
            class="rounded"
            :style="`background-color: ${category.color};`"
          />
        </span>
        <span class="desktop-only">
          {{ transaction.amount > 0 ? '+' : '' }}{{ $helper.toCurrency({
            value: transaction.amount, currencyCode:
              currency
          }) }}
        </span>
      </div>
    </template>

    <div
      class="custom-p-8 align-left"
      style="display: flex; flex-direction: column; gap: .2rem;"
    >
      <span
        v-if="transaction.contractor_contact?.name"
        class="small"
      >{{ transaction.contractor_contact.name }}</span>
      <span
        v-if="category"
        :style="`color: ${category.color}`"
        class="bold nowrap small text-ellipsis"
      >{{
        category.name }}</span>
      <span class="hint">{{ transaction.description || $t('noDesc') }}</span>
    </div>
  </DropdownWaFloating>
</template>

<style>
@media screen and (max-width: 1024px) {
  .icg-row {
    pointer-events: none;
  }
}

.icg-row:hover {
  opacity: .7;
}
</style>
