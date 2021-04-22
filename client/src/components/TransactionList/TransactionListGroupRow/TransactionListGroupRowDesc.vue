<template>
  <div
    class="flexbox middle space-4 semibold custom-mb-4"
    style="overflow: hidden"
  >
    <span
      v-if="daysBefore > 0"
      class="badge squared light-gray small c-time-diff-badge"
      >{{
        daysBefore === 1
          ? $t("tomorrow")
          : this.$moment(transaction.date).from($helper.currentDate)
      }}</span
    >
    <!-- if collapsed transactions header -->
    <template v-if="collapseHeaderData && transaction.external_source_info">
      <div
        class="text-ellipsis c-grouped-transaction-description"
        style="flex: 0 1 auto"
        :title="
          $t('collapseTransactionDesc', {
            category: category.name,
            count: collapseHeaderData.ids.length,
          })
        "
      >
        {{ transaction.external_source_info.name }}
      </div>
      <span
        class="badge light-gray small"
        :title="`${transaction.external_source_info.name}`"
        :style="`background:${transaction.external_source_info.color}`"
      >
        <span>&times;</span>
        {{ collapseHeaderData.ids.length }}
      </span>
      <span class="c-unfold-helper-icon">
        <i class="fas fa-caret-down opacity-50"></i>
      </span>
    </template>
    <!-- if repeating transactions in fututre -->
    <template v-else-if="isRepeatingGroup">
      <div
        class="black text-ellipsis"
        style="flex-shrink: 1"
        :title="
          $t('repeatingTransactionDesc', {
            repeats: transaction.affected_transactions,
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
      <span v-if="!transaction.description" class="light-gray">{{
        $t("noDesc")
      }}</span>
      <span v-if="transaction.repeating_id" :title="$t('repeatingTran')">
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
    daysBefore () {
      return this.$moment(this.transaction.date).diff(
        this.$helper.currentDate,
        'days'
      )
    }
  }
}
</script>
