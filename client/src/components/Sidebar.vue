<template>
  <div class="p-10 text-left">
    <div class="mb-10">
      <h4 class="font-bold mb-6">{{ $t('accounts') }}</h4>

      <div v-for="account in accounts" :key="account.id" class="mb-3">
        <div class="flex items-center justify-between">

          <router-link :to="{name: 'Account', params: {id: account.id}}">
            {{ account.name }}
          </router-link>

          <div
            v-if="account.stat"
            class="text-sm text-gray-500"
            v-html="
              `${account.stat.summaryShorten}&nbsp;${$helper.currToSymbol(
                account.currency
              )}`
            "
          ></div>
        </div>
      </div>
      <button
        @click="update('Account')"
        class="text-xs bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-3 border border-blue-500 hover:border-transparent rounded"
      >
        {{ $t('addAccount') }}
      </button>
    </div>

    <h4 class="font-bold mb-4">{{ $t('categories') }}</h4>

    <div class="uppercase font-bold text-xs mt-6 mb-4">{{ $t('income') }}</div>

    <div v-for="category in categoriesIncome" :key="category.id" class="mb-3">
      <div class="flex items-center">
        <div>
          <div
            class="w-2 h-2 rounded-full mr-1"
            :style="`background-color:${category.color};`"
          ></div>
        </div>
        <router-link :to="{name: 'Category', params: {id: category.id}}">
          {{ category.name }}
        </router-link>
      </div>
    </div>
    <button
      @click="update('Category')"
      class="text-xs bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-3 border border-blue-500 hover:border-transparent rounded"
    >
      {{ $t('addCategory') }}
    </button>

    <div class="uppercase font-bold text-xs mt-6 mb-4">{{ $t('expense') }}</div>

    <div v-for="category in categoriesExpense" :key="category.id" class="mb-3">
      <div class="flex items-center">
        <div>
          <div
            class="w-2 h-2 rounded-full mr-1"
            :style="`background-color:${category.color};`"
          ></div>
        </div>
        <router-link :to="{name: 'Category', params: {id: category.id}}">
          {{ category.name }}
        </router-link>
      </div>
    </div>

    <button
      @click="update('Category')"
      class="text-xs bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-3 border border-blue-500 hover:border-transparent rounded"
    >
      {{ $t('addCategory') }}
    </button>

    <Modal v-if="open" @close="close">
      <component :is="currentComponentInModal"></component>
    </Modal>
  </div>
</template>

<script>
import { mapState } from 'vuex'
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
      currentComponentInModal: ''
    }
  },
  computed: {
    ...mapState('account', ['accounts']),
    ...mapState('category', ['categories']),

    categoriesIncome () {
      return this.categories.filter(e => e.type === 'income')
    },

    categoriesExpense () {
      return this.categories.filter(e => e.type === 'expense')
    }
  },

  methods: {
    update (component) {
      this.open = true
      this.currentComponentInModal = component
    },

    close () {
      this.open = false
      this.currentComponentInModal = ''
    }
  }
}
</script>
