<template>
  <div class="flexbox space-16" style="justify-content: center;">
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
