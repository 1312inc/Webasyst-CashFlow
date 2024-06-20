<template>
  <div class="wide flexbox middle space-4 semibold">
    <div
      v-if="category"
      :style="`color:${category.color}`"
      class="c-item-category text-ellipsis"
    >
      {{ categoryComputed }}
    </div>
    <!-- if collapsed transactions header -->
    <template v-if="collapseHeaderData && transaction.external_source_info">
      <span
        v-if="transaction.external_source_info.entity_icon"
        :title="transaction.external_source_info.name"
        class="icon"
      >
        <img
          :src="transaction.external_source_info.entity_icon"
          alt=""
        >
      </span>
      <span
        v-else-if="transaction.external_source_info.glyph"
        :style="`color:${transaction.external_source_info.color}`"
        :title="transaction.external_source_info.name"
        class="icon"
      >
        <i
          :class="transaction.external_source_info.glyph"
          class="fas"
        />
      </span>
    </template>
    <!-- if repeating transactions in future -->
    <template v-else-if="isRepeatingGroup">
      <div
        class="black text-ellipsis"
        :title="
          $t('repeatingTransactionDesc', {
            repeats: transaction.affected_transactions
          })
        "
      >
        <span class="smaller custom-mr-4">
          <i class="fas fa-redo opacity-50" />
        </span>
      </div>
    </template>
    <!-- regular transaction -->
    <template v-else>
      <span
        v-if="transaction.external_source_info?.entity_icon"
        :title="transaction.external_source_info.name"
        class="icon"
      >
        <img
          :src="transaction.external_source_info.entity_icon"
          alt=""
        >
      </span>
      <span
        v-else-if="transaction.external_source_info?.glyph"
        :style="`color:${transaction.external_source_info.color}`"
        :title="transaction.external_source_info.name"
        class="icon"
      >
        <i
          :class="transaction.external_source_info.glyph"
          class="fas"
        />
      </span>
      <span
        v-if="transaction.repeating_id"
        :title="title"
      >
        <span class="smaller custom-mr-4">
          <i class="fas fa-redo opacity-50" />
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
    },

    categoryComputed () {
      const parent = this.category.parent_category_id
        ? `${
            this.$store.getters['category/getById'](
              this.category.parent_category_id
            ).name
          } / `
        : ''
      return `${parent}${this.category.name}`
    }
  }
}
</script>
