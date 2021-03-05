<template>
  <div>
    <button @click="handleComplete" class="red">
      <i class="fas fa-check"></i> {{ $t('Done') }}
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
            id: this.transactionId,
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
