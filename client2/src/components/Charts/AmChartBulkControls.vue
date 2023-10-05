<template>
  <div class="flexbox vertical">
    <div class="custom-mb-8">
      <AmChartBulkControlsArchive v-if="archive" />
      <AmChartBulkControlsRegular v-else />
    </div>
    <button
      @click="$_transactionActionsMixin_emptySelected"
      class="button nobutton small"
    >
      {{ $t("unselectAll") }}
    </button>
  </div>
</template>

<script>
import transactionActionsMixin from '@/mixins/transactionActionsMixin'
import AmChartBulkControlsArchive from './AmChartBulkControlsArchive'
import AmChartBulkControlsRegular from './AmChartBulkControlsRegular'

export default {
  mixins: [transactionActionsMixin],

  inject: {
    archive: { default: false }
  },

  components: {
    AmChartBulkControlsArchive,
    AmChartBulkControlsRegular
  }
}
</script>
