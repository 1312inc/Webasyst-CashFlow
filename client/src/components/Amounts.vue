<template>
  <div>
    <component :is="`AmountFor${target}`"
      v-for="type in types"
      :key="type"
      :type="type"
      v-bind="$attrs"
    />
  </div>
</template>

<script>
import AmountForPeriod from '@/components/AmountForPeriod'
import AmountForGroup from '@/components/AmountForGroup'
export default {
  props: {
    target: {
      type: String,
      require: true
    }
  },

  components: {
    AmountForPeriod,
    AmountForGroup
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
