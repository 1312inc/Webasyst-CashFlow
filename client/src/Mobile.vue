<template>
  <div id="wa-app">
    <div v-if="!$IsOnline.online">
      <span class="alert danger custom-mb-0" style="border-radius:0;"><span class="icon"><i class="fas fa-skull"></i></span>{{ $t('offlineMessage') }}</span>
    </div>
    <div class="content blank" style="overflow:hidden;">
      <div class="box contentbox">
        <router-view v-if="showView" />
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
      showView: false,
      open: false,
      currentComponentInModal: '',
      item: null
    }
  },

  async created () {
    await this.$store.dispatch('system/getCurrencies')
    await Promise.all([
      this.$store.dispatch('account/getList'),
      this.$store.dispatch('category/getList')
    ])
    this.showView = true
  },

  methods: {
    update (component, editedItem) {
      this.open = true
      this.currentComponentInModal = component
      this.item = editedItem
    },

    close () {
      window.android.goBack()
    }
  }
}
</script>
