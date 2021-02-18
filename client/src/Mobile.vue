<template>
  <div id="wa-app">
    <div v-if="!$IsOnline.online">
      <span class="alert danger custom-mb-0" style="border-radius:0;"><span class="icon"><i class="fas fa-skull"></i></span>{{ $t('offlineMessage') }}</span>
    </div>
    <div class="content blank" style="overflow:hidden;">
      <div sticky-container class="c-mobile-build">
        <router-view />
      </div>
    </div>
    <Modal v-if="open" @close="close">
      <component :is="currentComponentInModal" :editedItem="item"></component>
    </Modal>
  </div>
</template>

<script>
import Modal from '@/components/Modal'
import Account from '@/components/AddAccount'
import Category from '@/components/AddCategory'

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

  async created () {
    window.eventBus.$on('openDialog', (type = 'Category', editedItem = null) => {
      this.update(type, editedItem)
    })
  },

  methods: {
    update (componentName, editedItem) {
      this.open = true
      this.currentComponentInModal = componentName
      this.item = editedItem
    },

    close () {
      window.android.goBack()
    }

  }
}
</script>
