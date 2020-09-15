<template>
  <div class="p-10 text-left">
    <div class="mb-10">
      <h4 class="font-bold mb-2">Аккаунты</h4>

      <div
        v-for="account in accounts"
        :key="account.id"
        class="mb-1"
      >
        <div class="flex items-center">
          <div>
            <div
              class="w-2 h-2 rounded-full mr-1"
              :style="`background-color:${account.color};`"
            ></div>
          </div>
          <div class="text-sm">{{  account.name }}</div>
        </div>
      </div>
    </div>

    <h4 class="font-bold mb-4">Категории</h4>

    <div class="uppercase font-bold text-xs mt-6 mb-2">Income</div>

    <div
      v-for="category in categoriesIncome"
      :key="category.id"
      class="mb-1"
    >
      <div class="flex items-center">
        <div>
          <div
            class="w-2 h-2 rounded-full mr-1"
            :style="`background-color:${category.color};`"
          ></div>
        </div>
        <div class="text-sm">{{  category.name }}</div>
      </div>
    </div>

    <div class="uppercase font-bold text-xs mt-6 mb-2">Expense</div>

    <div
      v-for="category in categoriesExpense"
      :key="category.id"
      class="mb-1"
    >
      <div class="flex items-center">
        <div>
          <div
            class="w-2 h-2 rounded-full mr-1"
            :style="`background-color:${category.color};`"
          ></div>
        </div>
        <div class="text-sm">{{  category.name }}</div>
      </div>
    </div>

  </div>
</template>

<script>
import { mapState } from 'vuex'

export default {
  computed: {
    ...mapState('account', ['accounts']),
    ...mapState('category', ['categories']),

    categoriesIncome () {
      return this.categories.filter(e => e.type === 'income')
    },

    categoriesExpense () {
      return this.categories.filter(e => e.type === 'expense')
    }
  }
}
</script>
