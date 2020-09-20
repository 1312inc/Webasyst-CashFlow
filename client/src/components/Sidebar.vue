<template>
  <div class="p-10 text-left">
    <div class="mb-10">
      <h4 class="font-bold mb-2">Аккаунты</h4>

      <div v-for="account in accounts" :key="account.id" class="mb-1">
        <div class="flex items-center">
          <div>
            <div
              class="w-2 h-2 rounded-full mr-1"
              :style="`background-color:${account.color};`"
            ></div>
          </div>
          <div @click="update('Account', account.id)" class="text-sm">
            {{ account.name }}
          </div>
        </div>
      </div>
      <button
        @click="update('Account')"
        class="text-xs bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-3 border border-blue-500 hover:border-transparent rounded"
      >
        Добавить аккаунт
      </button>
    </div>

    <h4 class="font-bold mb-4">Категории</h4>

    <div class="uppercase font-bold text-xs mt-6 mb-2">Income</div>

    <div v-for="category in categoriesIncome" :key="category.id" class="mb-1">
      <div class="flex items-center">
        <div>
          <div
            class="w-2 h-2 rounded-full mr-1"
            :style="`background-color:${category.color};`"
          ></div>
        </div>
        <div @click="update('Category', category.id)" class="text-sm">
          {{ category.name }}
        </div>
      </div>
    </div>
    <button
      @click="update('Category')"
      class="text-xs bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-3 border border-blue-500 hover:border-transparent rounded"
    >
      Добавить категорию
    </button>

    <div class="uppercase font-bold text-xs mt-6 mb-2">Expense</div>

    <div v-for="category in categoriesExpense" :key="category.id" class="mb-1">
      <div class="flex items-center">
        <div>
          <div
            class="w-2 h-2 rounded-full mr-1"
            :style="`background-color:${category.color};`"
          ></div>
        </div>
        <div @click="update('Category', category.id)" class="text-sm">
          {{ category.name }}
        </div>
      </div>
    </div>

    <button
      @click="update('Category')"
      class="text-xs bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-1 px-3 border border-blue-500 hover:border-transparent rounded"
    >
      Добавить категорию
    </button>

    <Modal v-if="open" @close="close">
      <component
        v-bind:is="currentComponentInModal"
        :id="dataItemId"
      ></component>
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
      currentComponentInModal: '',
      dataItemId: null
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
    update (component, id = null) {
      this.currentComponentInModal = component
      this.dataItemId = id
      this.open = true
    },

    close () {
      this.open = false
      this.currentComponentInModal = ''
      this.dataItemId = null
    }
  }
}
</script>
