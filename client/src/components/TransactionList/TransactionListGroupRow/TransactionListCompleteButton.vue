<template>
  <div>
    <button @click="handleComplete" class="red nowrap rounded">
      <i class="fas fa-check"></i>
      <span class="desktop-only">
        {{ $t('Done') }}
      </span>
    </button>
  </div>
</template>

<script>
import api from '@/plugins/api'
export default {
  props: ['transactionId'],
  methods: {
    handleComplete () {
      api
        .post('cash.transaction.bulkComplete', {
          ids: [this.transactionId]
        })
        .then(() => {
          this.$store.commit('transaction/updateTransactionProps', {
            ids: [this.transactionId],
            props: {
              is_onbadge: null
            }
          })
        })
        .catch(e => {

        })
    }
  }
}
</script>
