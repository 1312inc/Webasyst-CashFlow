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
          <a @click.prevent.stop="handleComplete" href="#" class="custom-p-8">
            <i class="fas fa-check text-red"></i>
            <span>
              <span class="semibold">{{ $t("processToday") }}</span>
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
            href="#"
            class="custom-p-8"
          >
            <i class="fas fa-pencil-alt"></i
            ><span class="semibold">{{ $t("processEdits") }}</span></a
          >
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import api from '@/plugins/api'
export default {
  props: ['transaction', 'account'],
  data () {
    return {
      open: false
    }
  },
  methods: {
    handleComplete () {
      api
        .post('cash.transaction.bulkComplete', {
          ids: [this.transaction.id]
        })
        .then(() => {
          this.$store.commit('transaction/updateTransactionProps', {
            ids: [this.transaction.id],
            props: {
              is_onbadge: null,
              date: this.$moment().format('LL') // set current date
            }
          })
        })
        .catch(e => {})
    }
  }
}
</script>
