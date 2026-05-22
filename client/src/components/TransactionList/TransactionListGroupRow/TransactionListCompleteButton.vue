<template>
  <DropdownWaFloating>
    <template #toggler>
      <button class="light-gray small nowrap rounded custom-m-0">
        <i class="fas fa-check-circle text-red" />
        <span
          v-if="!isFixed"
          class="desktop-only"
        >
          {{ $t("Process") }}
          <i class="fas fa-chevron-down text-light-gray fa-xs" />
        </span>
      </button>
    </template>
    <ul class="menu">
      <li>
        <a
          class="custom-p-8"
          @click.prevent.stop="handleComplete($moment().format('YYYY-MM-DD'))"
        >
          <i class="fas fa-check text-red" />
          <span>
            <span class="semibold black nowrap">{{ $t("processToday") }}</span>
            <p class="hint custom-mt-4">
              {{
                $t("amountOnDate", {
                  amount: $helper.toCurrency({
                    value: transaction.amount,
                    currencyCode: account.currency
                  }),
                  date: $moment().format("LL")
                })
              }}
            </p>
          </span>
        </a>
      </li>
      <li v-if="transaction.date !== $moment().format('YYYY-MM-DD')">
        <a
          class="custom-p-8"
          @click.prevent.stop="handleComplete($moment(transaction.date).format('YYYY-MM-DD'))"
        >
          <i class="fas fa-check text-red" />
          <span>
            <span class="semibold black nowrap">{{ $t("Process") }} {{ $moment(transaction.date).format("LL") }}</span>
            <p class="hint custom-mt-4">
              {{
                $t("amountOnDate", {
                  amount: $helper.toCurrency({
                    value: transaction.amount,
                    currencyCode: account.currency
                  }),
                  date: $moment(transaction.date).format("LL")
                })
              }}
            </p>
          </span>
        </a>
      </li>
      <li>
        <a
          class="custom-p-8"
          @click.prevent.stop="$emit('processEdit')"
        >
          <i class="fas fa-pencil-alt" /><span class="semibold black nowrap">{{ $t("processEdits") }}</span></a>
      </li>
    </ul>
  </DropdownWaFloating>
</template>

<script setup>
import DropdownWaFloating from '@/components/Inputs/DropdownWaFloating.vue'

defineProps(['transaction', 'account', 'isFixed'])

</script>

<script>
/* eslint-disable camelcase */
export default {

  methods: {
    handleComplete (date) {
      this.$store
        .dispatch('transaction/update', {
          ...this.transaction,
          apply_to_all_in_future: false,
          date,
          is_onbadge: false,
          external: {
            source: this.transaction.external_source || null,
            id: this.transaction.external_id || null
          }
        })
    }
  }
}
</script>
