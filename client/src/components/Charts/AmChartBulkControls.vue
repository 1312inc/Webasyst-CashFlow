<template>
  <div class="flexbox vertical">
    <div class="flexbox space-16 custom-mb-4">
      <ButtonRound
        @click.native="openMove = true"
        icon="fa-arrow-right"
        :tooltip="$t('move')"
      />
      <ButtonRound
        @click.native="$_transactionActionsMixin_bulkDelete"
        icon="fa-trash-alt"
        :tooltip="$t('delete')"
        class="red"
      />
    </div>
    <button
      @click="$_transactionActionsMixin_emptySelected"
      class="button nobutton small"
    >
      {{ $t("unselectAll") }}
    </button>

    <portal>
      <Modal v-if="openMove" @close="openMove = false">
        <TransactionMove />
      </Modal>
    </portal>
  </div>
</template>

<script>
import transactionActionsMixin from '@/mixins/transactionActionsMixin'
import Modal from '@/components/Modal'
import TransactionMove from '@/components/Modals/TransactionMove'
import ButtonRound from '@/components/Buttons/ButtonRound'

export default {
  mixins: [transactionActionsMixin],
  components: {
    Modal,
    TransactionMove,
    ButtonRound
  },
  data () {
    return {
      openMove: false
    }
  }
}
</script>

<style scoped>
.flexbox {
  justify-content: center;
}
button.nobutton {
  color: var(--gray);
}
button.nobutton:hover {
  color: var(--light-gray);
}
</style>
