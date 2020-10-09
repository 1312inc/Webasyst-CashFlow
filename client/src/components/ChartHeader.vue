<template>
  <div v-if="currentCategory" class="mb-4">
    <div class="flex items-center">
      <div class="text-2xl">{{ currentCategory.name }}</div>
      <div
        v-if="currentCategory.stat && currentCategory.stat.summary"
        class="text-2xl rounded p-2 ml-4"
        :class="`bg-${currentCategory.stat.summary > 0 ? 'green' : 'red'}-300`"
      >
        {{ $numeral(currentCategory.stat.summary).format() }}
        {{ $helper.currToSymbol(currentCategory.currency) }}
      </div>
      <div class="ml-4">
        <button @click="update(currentCategory)" class="text-indigo-500">
          изменить настройки
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
