<template>
  <div
    class="flexbox space-16"
    style="justify-content: center;"
  >
    <ButtonRound
      icon="fa-trash-restore-alt"
      :tooltip="$t('restore')"
      @click.native="restore"
    />
    <ButtonRound
      icon="fa-trash-alt"
      :tooltip="$t('purge')"
      class="red"
      @click.native="purge"
    />
  </div>
</template>

<script>
import ButtonRound from '@/components/Buttons/ButtonRound'

export default {
  components: {
    ButtonRound
  },

  methods: {
    restore () {
      this.$store.dispatch('transactionBulk/restore')
    },

    purge () {
      if (confirm(this.$t('bulkPurgeWarning'))) {
        this.$store.dispatch('transactionBulk/purge')
      }
    }
  }
}
</script>
