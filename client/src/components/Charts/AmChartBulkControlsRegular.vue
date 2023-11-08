<template>
  <div
    class="flexbox space-16"
    style="justify-content: center;"
  >
    <ButtonRound
      icon="fa-arrow-right"
      :tooltip="$t('move')"
      @click.native="openMove = true"
    />
    <ButtonRound
      icon="fa-trash-alt"
      :tooltip="$t('delete')"
      class="red"
      @click.native="$_transactionActionsMixin_bulkDelete"
    />
    <portal>
      <Modal
        v-if="openMove"
        @close="openMove = false"
      >
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

  components: {
    Modal,
    TransactionMove,
    ButtonRound
  },
  mixins: [transactionActionsMixin],

  data () {
    return {
      openMove: false
    }
  }
}
</script>
