<template>
  <div
    class="flexbox space-16"
    style="justify-content: center;"
  >
    <ButtonRound
      icon="fa-trash-restore-alt"
      :tooltip="$t('restore')"
      :disabled="archiveMutationPending"
      @click.native="restore"
    />
    <ButtonRound
      icon="fa-trash-alt"
      :tooltip="$t('purge')"
      :disabled="archiveMutationPending"
      class="red"
      @click.native="$_transactionActionsMixin_confirmBulkRemoval('purge', 'bulkPurgeWarning', 'purge')"
    />
  </div>
</template>

<script>
import ButtonRound from '@/components/Buttons/ButtonRound'
import transactionActionsMixin from '@/mixins/transactionActionsMixin'

export default {
  components: {
    ButtonRound
  },
  mixins: [transactionActionsMixin],

  computed: {
    archiveMutationPending () {
      return this.$_transactionActionsMixin_removalPending || this.$store.state.transactionBulk.restorePending
    }
  },

  methods: {
    restore () {
      this.$store.dispatch('transactionBulk/restore')
    }
  }
}
</script>
