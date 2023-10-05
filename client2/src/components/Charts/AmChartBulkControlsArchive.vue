<template>
  <div class="flexbox space-16" style="justify-content: center;">
    <ButtonRound
      @click.native="restore"
      icon="fa-trash-restore-alt"
      :tooltip="$t('restore')"
    />
    <ButtonRound
      @click.native="purge"
      icon="fa-trash-alt"
      :tooltip="$t('purge')"
      class="red"
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
