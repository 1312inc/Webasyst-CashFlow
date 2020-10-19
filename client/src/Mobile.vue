<template>
  <div id="wa-app">
    <div class="content blank" style="overflow:hidden;">
      <div class="box contentbox">
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

  async mounted () {
    window.eventBus.$on('openDialog', (type = 'Category', editedItem = null) => {
      this.update(type, editedItem)
    })

    await Promise.all([
      this.$store.dispatch('account/getList'),
      this.$store.dispatch('category/getList')
    ])

    this.$store.commit('setCurrentType', {
      name: this.$route.name,
      id: +this.$route.params.id
    })

    this.$store.dispatch('transaction/resetAllDataToInterval', {
      from: this.$moment().add(-1, 'M').format('YYYY-MM-DD'),
      to: this.$moment().add(1, 'M').format('YYYY-MM-DD')
    })
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
