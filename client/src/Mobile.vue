<template>
  <div
    id="wa-app"
    class="c-mobile-build"
  >
    <div v-if="!$IsOnline.online">
      <span
        class="alert danger custom-mb-0"
        style="border-radius:0;"
      ><span class="icon"><i class="fas fa-skull" /></span>{{ $t('offlineMessage') }}</span>
    </div>
    <div
      class="content blank"
      style="display: flex; flex-direction: column; min-height: 100vh;"
    >
      <router-view />
    </div>
    <Modal
      v-if="open"
      @close="close"
    >
      <component
        :is="currentComponentInModal"
        :edited-item="item"
      />
    </Modal>
  </div>
</template>

<script>
import Modal from '@/components/Modal'
import Account from '@/components/Modals/AddAccount'
import Category from '@/components/Modals/AddCategory'

export default {
  components: {
    Modal,
    Account,
    Category
  },

  data () {
    return {
      open: false,
      currentComponentInModal: '',
      item: null
    }
  },

  created () {
    this.addEmitterHandlers()
  },

  methods: {
    update (componentName, editedItem) {
      this.open = true
      this.currentComponentInModal = componentName
      this.item = editedItem
    },

    close () {
      window.emitter.emit('closeDialog')
    },

    addEmitterHandlers () {
      window.emitter.on('openDialog', (type = 'Category', editedItem = null) => {
        this.update(type, editedItem)
      })

      window.emitter.on('multiSelectEnabled', (enable) => {
        this.$store.commit('setMultiSelectMode', enable)
      })
    }

  }
}
</script>
