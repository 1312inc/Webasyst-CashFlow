<template>
  <div id="wa-app">
    <div v-if="!$IsOnline.online">
      <span class="alert danger custom-mb-0" style="border-radius:0;"><span class="icon"><i class="fas fa-skull"></i></span>{{ $t('offlineMessage') }}</span>
    </div>
    <div class="content blank" style="overflow:hidden;">
      <div class="c-mobile-build">
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

    // await this.$store.dispatch('system/getCurrencies')
    // await Promise.all([
    //   this.$store.dispatch('account/getList'),
    //   this.$store.dispatch('category/getList')
    // ])

    const from = this.getDate(
      'from',
      this.$moment().add(-1, 'Y').format('YYYY-MM-DD')
    )

    const to = this.getDate(
      'to',
      this.$moment().add(6, 'M').format('YYYY-MM-DD')
    )

    this.$store.commit('transaction/updateQueryParams', { from, to })
  },

  methods: {
    update (componentName, editedItem) {
      this.open = true
      this.currentComponentInModal = componentName
      this.item = editedItem
    },

    close () {
      window.android.goBack()
    },

    getDate (type, defaultDate) {
      let result = defaultDate
      const lsValue = localStorage.getItem(`interval_${type}`)
      if (lsValue) {
        result = this.$store.state.intervals[type].find((e) => e.key === lsValue)?.value || defaultDate
      }
      return result
    }
  }
}
</script>
