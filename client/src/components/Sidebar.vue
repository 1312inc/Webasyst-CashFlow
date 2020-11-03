<template>
  <div class="sidebar flexbox width-16rem tw-z-50">
    <div class="sidebar-header">
      <transition name="fade-appear">
        <div v-if="accounts.length > 1" class="tw-mt-6">
          <div v-if="currenciesInAccounts.length > 1" class="tw-mx-4">
            <h5>{{ $t("cashOnHand") }}</h5>
          </div>
          <ul class="menu-v custom-mb-0">
            <li
              v-for="(currency, i) in currenciesInAccounts"
              :key="i"
              :class="{ selected: isActive('Currency', currency) }"
            >
              <router-link
                :to="`/currency/${currency}`"
                :class="{ bold: currenciesInAccounts.length === 1 }"
                >{{
                  currenciesInAccounts.length > 1 ? currency : $t("cashOnHand")
                }}</router-link
              >
            </li>
          </ul>
        </div>
      </transition>
    </div>

    <div class="sidebar-body">
      <transition name="fade-appear">
        <div v-if="accounts.length" class="tw-mt-6">
          <div class="tw-mx-4">
            <h5>{{ $t("accounts") }}</h5>
          </div>

          <draggable
            group="accounts"
            tag="ul"
            :list="accounts"
            @update="sortAccounts()"
            class="menu-v"
          >
            <li
              v-for="account in accounts"
              :key="account.id"
              :class="{ selected: isActive('Account', account.id) }"
            >
              <router-link
                :to="`/account/${account.id}`"
                class="flexbox middle"
              >
                <div class="icon">
                  <img
                    v-if="$helper.isValidHttpUrl(account.icon)"
                    :src="account.icon"
                    alt=""
                  />
                  <span v-else>
                    <i class="fas fa-star"></i>
                  </span>
                </div>
                <span>{{ account.name }}</span>
                <span
                  v-if="account.stat"
                  class="count"
                  v-html="
                    `${
                      account.stat.summaryShorten
                    }&nbsp;${getCurrencySignByCode(account.currency)}`
                  "
                ></span>
              </router-link>
            </li>
          </draggable>

          <div class="tw-mx-4">
            <button @click="update('Account')" class="button rounded smaller">
              <i class="fas fa-plus"></i> {{ $t("addAccount") }}
            </button>
          </div>
        </div>
      </transition>

      <transition name="fade-appear">
        <div v-if="categories.length" class="tw-mt-6">
          <div class="tw-mx-4">
            <h5>{{ $t("categories") }}</h5>
          </div>
          <h6 class="heading black">{{ $t("income") }}</h6>

          <draggable
            group="categoriesIncome"
            tag="ul"
            :list="categoriesIncome"
            @update="sortCategories(categoriesIncome)"
            class="menu-v"
          >
            <li
              v-for="category in categoriesIncome"
              :key="category.id"
              :class="{ selected: isActive('Category', category.id) }"
            >
              <router-link
                :to="`/category/${category.id}`"
                class="flexbox middle"
              >
                <span class="icon"
                  ><i
                    class="rounded"
                    :style="`background-color:${category.color};`"
                  ></i
                ></span>
                <span>{{ category.name }}</span>
              </router-link>
            </li>
          </draggable>

          <h6 class="heading black">{{ $t("expense") }}</h6>

          <draggable
            group="categoriesExpense"
            tag="ul"
            :list="categoriesExpense"
            @update="sortCategories(categoriesExpense)"
            class="menu-v"
          >
            <li
              v-for="category in categoriesExpense"
              :key="category.id"
              :class="{ selected: isActive('Category', category.id) }"
            >
              <router-link
                :to="`/category/${category.id}`"
                class="flexbox middle"
              >
                <span class="icon"
                  ><i
                    class="rounded"
                    :style="`background-color:${category.color};`"
                  ></i
                ></span>
                <span>{{ category.name }}</span>
              </router-link>
            </li>
          </draggable>

          <div class="tw-mx-4">
            <button @click="update('Category')" class="button rounded smaller">
              <i class="fas fa-plus"></i> {{ $t("addCategory") }}
            </button>
          </div>
        </div>
      </transition>
    </div>
    <div class="sidebar-footer">
      <ul class="menu-v">
        <li>
          <a :href="`${$helper.baseUrl}import/`">
            <i class="fas fa-file-import"></i>
            <span>Импорт</span>
          </a>
        </li>
      </ul>
    </div>

    <Modal v-if="open" @close="close">
      <component :is="currentComponentInModal"></component>
    </Modal>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import draggable from 'vuedraggable'
import Modal from '@/components/Modal'
import Account from '@/components/AddAccount'
import Category from '@/components/AddCategory'

export default {
  components: {
    draggable,
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

    ...mapGetters('system', ['getCurrencySignByCode']),
    ...mapGetters('account', ['currenciesInAccounts']),
    ...mapGetters('category', ['getByType']),

    categoriesIncome () {
      return this.getByType('income')
    },

    categoriesExpense () {
      return this.getByType('expense')
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
    },

    isActive (name, id) {
      if (this.$route) {
        if (
          this.$route.name === 'Home' &&
          id === this.currenciesInAccounts[0]
        ) {
          return true
        }
        return (
          this.$route.name === name &&
          (+this.$route.params.id || this.$route.params.id) === id
        )
      }
    },

    sortAccounts () {
      const ids = this.accounts.map((e) => e.id)
      this.$store.dispatch('account/sort', {
        order: ids
      })
    },

    sortCategories (list) {
      const ids = list.map((e) => e.id)
      this.$store.commit('category/updateSort', list)
      this.$store.dispatch('category/sort', {
        order: ids
      })
    }
  }
}
</script>
