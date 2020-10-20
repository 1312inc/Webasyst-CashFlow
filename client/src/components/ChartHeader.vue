<template>
  <div v-if="currentCategory">
    <div class="flexbox middle space-1rem">
      <div class="h2 custom-mb-0">{{ currentCategory.name }}</div>
      <div
        v-if="currentCategory.stat && currentCategory.stat.summary"
        class="larger"
      >
        {{ $numeral(currentCategory.stat.summary).format() }}
        {{ $helper.currToSymbol(currentCategory.currency) }}
      </div>
      <div>
        <button @click="update(currentCategory)" class="button nobutton small">
          {{ $t("changeSettings") }}
        </button>
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

  computed: {
    currentCategory () {
      return this.$store.getters.getCurrentType
    }
  },

  methods: {
    update (item) {
      this.open = true
      this.currentComponentInModal = item.currency ? 'Account' : 'Category'
      this.item = item
    },

    close () {
      this.open = false
      this.currentComponentInModal = ''
    }
  }
}
</script>
