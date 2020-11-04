<template>
  <div id="wa-app">
    <div v-if="!$IsOnline.online">
      <span class="alert danger custom-mb-0" style="border-radius:0;"><span class="icon"><i class="fas fa-skull"></i></span>{{ $t('offlineMessage') }}</span>
    </div>
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

  async created () {
    await this.$store.dispatch('system/getCurrencies')

    await Promise.all([
      this.$store.dispatch('account/getList'),
      this.$store.dispatch('category/getList')
    ])

    const from = this.getDate(
      'from',
      this.$moment().add(-1, 'Y').format('YYYY-MM-DD')
    )

    const to = this.getDate(
      'to',
      this.$moment().add(6, 'M').format('YYYY-MM-DD')
    )

    const filter = this.$store.state.transaction.queryParams.filter || `currency/${this.$store.getters['account/currenciesInAccounts'][0]}`

    this.$store.subscribe((mutation) => {
      if (mutation.type === 'transaction/updateQueryParams') {
        this.$store.dispatch('transaction/getList')

        const keys = Object.keys(mutation.payload)
        const key = keys[0]
        const changeOffset = keys.length === 1 && key === 'offset'

        if (!changeOffset) {
          this.$store.dispatch('transaction/getChartData')
        }
      }
    })

    this.$store.commit('transaction/updateQueryParams', { from, to, filter })
  },

  methods: {
    update (component, editedItem) {
      this.open = true
      this.currentComponentInModal = component
      this.item = editedItem
    },

    close () {
      window.android.goBack()
    },

    getDate (type, defaultDate) {
      let result = defaultDate
      const lsValue = localStorage.getItem(`interval_${type}`)
      if (lsValue) {
        result = this.$store.state.intervals[type].find((e) => e.title === lsValue)?.value || defaultDate
      }
      return result
    }
  }
}
</script>
