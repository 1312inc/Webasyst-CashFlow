<template>
  <div class="flexbox vertical">
    <div class="custom-mb-8">
      <AmChartBulkControlsArchive v-if="archive" />
      <AmChartBulkControlsRegular v-else />
    </div>
    <button
      class="button nobutton small"
      @click="$_transactionActionsMixin_emptySelected"
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

  components: {
    AmChartBulkControlsArchive,
    AmChartBulkControlsRegular
  },
  mixins: [transactionActionsMixin],

  inject: {
    archive: { default: false }
  }
}
</script>
