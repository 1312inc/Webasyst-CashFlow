<template>
  <div
    @mouseover="open = true"
    @mouseleave="open = false"
    :class="{ 'is-opened': open }"
    class="dropdown"
  >
    <button @click.stop="" class="light-gray small nowrap rounded">
      <i class="fas fa-check"></i>
      <span class="desktop-only">
        {{ $t("Process") }}
      </span>
    </button>
    <div class="dropdown-body">
      <ul class="menu">
        <li>
          <a @click.prevent.stop="handleComplete" href="#" class="custom-p-8">
            <div>
              <div>
                <i class="fas fa-check"></i
                ><span>{{ $t("processToday") }}</span>
              </div>
              <span class="hint">
                {{
                  $t("amountOnDate", {
                    amount: $helper.toCurrency({
                      value: transaction.amount,
                      currencyCode: account.currency
                    }),
                    date: $moment(transaction.date).format("LL")
                  })
                }}
              </span>
            </div>
          </a>
        </li>
        <li>
          <a
            @click.prevent.stop="$emit('processEdit')"
            href="#"
            class="custom-p-8"
          >
            <i class="fas fa-pencil-alt"></i
            ><span>{{ $t("processEdits") }}</span></a
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
