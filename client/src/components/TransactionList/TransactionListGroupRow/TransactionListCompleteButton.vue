<template>
  <div
    @mouseover="open = true"
    @mouseleave="open = false"
    :class="{ 'is-opened': open }"
    class="dropdown"
  >
    <button @click.stop="" class="light-gray small nowrap rounded">
      <i class="fas fa-check-circle text-red"></i>
      <span class="desktop-only">
        {{ $t("Process") }}
         <i class="fas fa-chevron-down text-light-gray fa-xs"></i>
      </span>
    </button>
    <div class="dropdown-body right" style="min-width: 250px;">
      <ul class="menu">
        <li>
          <a @click.prevent.stop="handleComplete($moment().format('YYYY-MM-DD'))" class="custom-p-8">
            <i class="fas fa-check text-red"></i>
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
        <li>
          <a @click.prevent.stop="handleComplete($moment(transaction.date).format('YYYY-MM-DD'))" class="custom-p-8">
            <i class="fas fa-check text-red"></i>
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
            @click.prevent.stop="$emit('processEdit')"
            class="custom-p-8"
          >
            <i class="fas fa-pencil-alt"></i
            ><span class="semibold black">{{ $t("processEdits") }}</span></a
          >
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
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
