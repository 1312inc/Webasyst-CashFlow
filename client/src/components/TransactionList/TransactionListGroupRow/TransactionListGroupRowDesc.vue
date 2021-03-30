<template>
  <div class="flexbox space-4 semibold custom-mb-8" style="overflow: hidden">
    <template v-if="collapseHeaderData">
      <div class="black text-ellipsis" style="flex-shrink: 1">
        {{
          $t("collapseTransactionDesc", { category: category.name, count: 4 })
        }}
      </div>
      <span>
        <i class="fas fa-chevron-circle-down opacity-50"></i>
      </span>
    </template>
    <template v-else>
      <div
        v-if="transaction.description"
        class="black text-ellipsis"
        style="flex-shrink: 1"
      >
        {{ transaction.description }}
      </div>
      <span v-if="!transaction.description" class="gray">{{
        $t("noDesc")
      }}</span>
      <span v-if="transaction.repeating_id" :title="$t('repeatingTran')">
        <i class="fas fa-redo-alt opacity-50"></i>
      </span>
    </template>
  </div>
</template>

<script>
export default {
  props: ['transaction', 'collapseHeaderData', 'category']
}
</script>
