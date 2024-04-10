<template>
  <div
    v-on-click-outside="() => { if (isTouch) open = false}"
    @mouseover="() => { if (!isTouch) open = true}"
    @mouseleave="() => { if (!isTouch) open = false}"
  >
    <button
      ref="reference"
      class="light-gray small nowrap rounded custom-m-0"
      @click.stop="() => { if (isTouch) open = !open}"
    >
      <i class="fas fa-check-circle text-red" />
      <span
        v-if="!isFixed"
        class="desktop-only"
      >
        {{ $t("Process") }}
        <i class="fas fa-chevron-down text-light-gray fa-xs" />
      </span>
    </button>
    <div
      v-if="open"
      ref="floating"
      class="dropdown is-opened"
      style="z-index: 9999;"
      :style="floatingStyles"
    >
      <div
        class="dropdown-body"
        style="min-width: 250px;"
      >
        <ul class="menu">
          <li>
            <a
              class="custom-p-8"
              @click.prevent.stop="handleComplete($moment().format('YYYY-MM-DD'))"
            >
              <i class="fas fa-check text-red" />
              <span>
                <span class="semibold black">{{ $t("processToday") }}</span>
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
                <span class="semibold black">{{ $t("Process") }} {{ $moment(transaction.date).format("LL") }}</span>
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
              <i class="fas fa-pencil-alt" /><span class="semibold black">{{ $t("processEdits") }}</span></a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  useFloating,
  flip,
  shift
} from '@floating-ui/vue'
import { vOnClickOutside } from '@vueuse/components'
import { ref } from 'vue'

const props = defineProps(['transaction', 'account', 'isFixed'])

const reference = ref(null)
const floating = ref(null)
const open = ref(false)
const { floatingStyles } = useFloating(reference, floating, {
  placement: 'bottom-start',
  middleware: [flip(), shift()]
})

const isTouch = 'ontouchstart' in window

</script>

<script>
/* eslint-disable camelcase */
export default {

  methods: {
    handleComplete (date) {
      const {
        id,
        amount,
        account_id,
        category_id
      } = this.transaction
      this.$store
        .dispatch('transaction/update', {
          id,
          apply_to_all_in_future: false,
          amount,
          account_id,
          category_id,
          date,
          is_onbadge: false
        })
    }
  }
}
</script>

<style scoped>
.dropdown-body {
  position: relative;
  display: block;
  left: auto;
  top: auto;
}
</style>
