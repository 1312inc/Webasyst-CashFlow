<template>
  <div>
    <AmountForPeriod
      v-for="type in types"
      :key="type"
      :type="type"
      v-bind="$attrs"
    />
  </div>
</template>

<script>
import AmountForPeriod from '@/components/AmountForPeriod'
export default {
  components: {
    AmountForPeriod
  },

  computed: {
    currentEntity () {
      return this.$store.getters.getCurrentType
    },

    types () {
      if (this.currentEntity?.is_profit) {
        return ['profit']
      } else if (this.currentEntity?.type) {
        return [this.currentEntity.type]
      } else {
        return ['income', 'expense', 'profit']
      }
    }
  }
}
</script>
