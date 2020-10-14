<template>
  <div>
    <div class="box">
      <h5>{{ $t("accounts") }}</h5>
      <ul class="menu-v">
        <li v-for="account in accounts" :key="account.id">
          <router-link :to="{ name: 'Account', params: { id: account.id } }">
            <span>{{ account.name }}</span>
            <span
              v-if="account.stat"
              class="count"
              v-html="
                `${account.stat.summaryShorten}&nbsp;${$helper.currToSymbol(
                  account.currency
                )}`
              "
            ></span>
          </router-link>
        </li>
      </ul>

      <button @click="update('Account')" class="button rounded smaller">
        <i class="fas fa-plus"></i> {{ $t("addAccount") }}
      </button>
    </div>

    <div class="box">
      <h5>{{ $t("categories") }}</h5>
      <h6 class="heading black">{{ $t("income") }}</h6>

      <ul class="menu-v">
        <li v-for="category in categoriesIncome" :key="category.id">
          <router-link :to="{ name: 'Category', params: { id: category.id } }" class="flexbox middle">
            <span class="icon"
              ><i
                class="rounded"
                :style="`background-color:${category.color};`"
              ></i
            ></span>
            <span>{{ category.name }}</span>
          </router-link>
        </li>
      </ul>

      <!-- <button @click="update('Category')" class="button rounded smaller">
        <i class="fas fa-plus"></i> {{ $t("addCategory") }}
      </button> -->

      <h6 class="heading black">{{ $t("expense") }}</h6>

      <ul class="menu-v">
        <li v-for="category in categoriesExpense" :key="category.id">
          <router-link :to="{ name: 'Category', params: { id: category.id } }" class="flexbox middle">
            <span class="icon"
              ><i
                class="rounded"
                :style="`background-color:${category.color};`"
              ></i
            ></span>
            <span>{{ category.name }}</span>
          </router-link>
        </li>
      </ul>

      <button @click="update('Category')" class="button rounded smaller">
        <i class="fas fa-plus"></i> {{ $t("addCategory") }}
      </button>
    </div>

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
      return this.categories.filter((e) => e.type === 'income')
    },

    categoriesExpense () {
      return this.categories.filter((e) => e.type === 'expense')
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
