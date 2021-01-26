<template>
  <div class="flexbox space-12">
    <div
      v-for="account in accounts"
      :key="account.id"
      :class="{
        'text-green': type === 'income',
        'text-red': type === 'expense',
      }"
    >
      <div>
        <i
          class="fas"
          :class="{
            'fa-caret-up': type === 'income',
            'fa-caret-down': type === 'expense',
          }"
        ></i
        >&nbsp;<span class="small">{{
          $helper.toCurrency(
            getTotalByAccout(account.id),
            account.currency,
            true
          )
        }}</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    group: {
      type: Array,
      required: true
    },

    type: {
      type: String,
      required: true
    }
  },

  computed: {
    accounts () {
      return this.group.reduce((acc, e) => {
        const account = this.$store.getters['account/getById'](e.account_id)
        if (account && !acc.includes(account)) {
          acc.push(account)
        }
        return acc
      }, [])
    }
  },

  methods: {
    getTotalByAccout (accountId) {
      return this.group
        .filter(
          e =>
            (this.type === 'income' ? e.amount >= 0 : e.amount < 0) &&
            e.account_id === accountId
        )
        .reduce((acc, e) => {
          return acc + e.amount
        }, 0)
    }
  }
}
</script>
