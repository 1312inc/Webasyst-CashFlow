<template>
  <div
    :class="{ 'is-opened': open }"
    class="dropdown"
    @mouseover="open = true"
    @mouseleave="open = false"
  >
    <button
      class="light-gray small nowrap rounded"
      @click.stop=""
    >
      <i class="fas fa-check-circle text-red" />
      <span class="desktop-only">
        {{ $t("Process") }}
        <i class="fas fa-chevron-down text-light-gray fa-xs" />
      </span>
    </button>
    <div
      class="dropdown-body right"
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
</template>

<script>
/* eslint-disable camelcase */
export default {
  props: ['transaction', 'account'],
  data () {
    return {
      open: false
    }
  },
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
