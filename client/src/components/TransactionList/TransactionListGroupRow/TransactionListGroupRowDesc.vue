<template>
  <div
    class="flexbox middle space-4 semibold custom-mb-4"
    style="overflow: hidden"
  >
    <div
      v-if="category.name"
      :style="`color:${category.color}`"
      class="text-ellipsis"
    >
      {{ category.name }}
    </div>
    <!-- if collapsed transactions header -->
    <template v-if="collapseHeaderData && transaction.external_source_info">
      <div
        class="text-ellipsis c-grouped-transaction-description"
        style="flex: 0 1 auto"
        :title="
          $t('collapseTransactionDesc', {
            category: category.name,
            count: collapseHeaderData.ids.length
          })
        "
      >
        {{ transaction.external_source_info.name }}
      </div>
      <span
        v-if="transaction.external_source_info"
        :style="`color:${transaction.external_source_info.color}`"
      >
        <i :class="transaction.external_source_info.glyph" class="fas"></i>
      </span>
    </template>
    <!-- if repeating transactions in fututre -->
    <template v-else-if="isRepeatingGroup">
      <div
        class="black text-ellipsis"
        style="flex-shrink: 1"
        :title="
          $t('repeatingTransactionDesc', {
            repeats: transaction.affected_transactions
          })
        "
      >
        {{ transaction.description }}
      </div>
    </template>
    <!-- mormal transaction -->
    <template v-else>
      <div
        v-if="transaction.description"
        class="black text-ellipsis"
        style="flex-shrink: 1"
      >
        {{ transaction.description }}
      </div>
      <span v-if="transaction.repeating_id" :title="title">
        <span class="small custom-mr-4">
          <i class="fas fa-redo opacity-50"></i>
        </span>
      </span>
    </template>
  </div>
</template>

<script>
export default {
  props: ['transaction', 'collapseHeaderData', 'isRepeatingGroup', 'category'],

  computed: {
    title () {
      return this.$tc(
        `repeatingInfo.interval.${this.transaction.repeating_data.interval}`,
        this.transaction.repeating_data.frequency,
        {
          frequency: this.transaction.repeating_data.frequency
        }
      )
    }
  }
}
</script>
