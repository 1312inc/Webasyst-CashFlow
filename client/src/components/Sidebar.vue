<template>
  <div class="sidebar flexbox width-16rem tw-z-50">

    <div class="sidebar-header">
      <SearchField />
    </div>

    <div class="sidebar-body">
        <!-- Widgets charts block -->
        <div v-if="currenciesInAccounts.length" class="custom-mt-24">
          <div v-if="currenciesInAccounts.length > 1" class="tw-mx-4">
            <h5>{{ $t("cashOnHand") }}</h5>
          </div>
          <ul class="menu">
            <li
              v-for="currency in currenciesInAccounts"
              :key="currency"
            >
              <router-link
                :to="`/currency/${currency}`"
                :class="{ bold: currenciesInAccounts.length === 1 }"
                class="flexbox middle full-width"
              >
                <div class="wide">
                  {{
                    currenciesInAccounts.length > 1
                      ? currency
                      : $t("cashOnHand")
                  }}
                </div>
                <CurrencyChart :currency="currency" />
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Accounts list block -->
        <div v-if="accounts.length" class="custom-mt-24">
          <draggable
            group="accounts"
            tag="ul"
            :list="accounts"
            @update="sortAccounts()"
            class="menu"
          >
            <li
              v-for="account in accounts"
              :key="account.id"
            >
              <router-link
                :to="`/account/${account.id}`"
                class="flexbox middle"
              >
                <span class="icon">
                  <img
                    v-if="$helper.isValidHttpUrl(account.icon)"
                    :src="account.icon"
                    alt=""
                    class="size-20"
                  />
                  <span v-else>
                    <i class="fas fa-star"></i>
                  </span>
                </span>
                <span>{{ account.name }}</span>
                <span
                  v-if="account.stat"
                  class="count"
                  v-html="
                    `${
                      account.stat.balanceShorten
                    }&nbsp;${$helper.currencySignByCode(account.currency)}`
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

        <!-- Categories list block -->
        <div v-if="categories.length" class="custom-mt-24">
          <h6 class="heading black">{{ $t("income") }}</h6>

          <draggable
            group="categoriesIncome"
            tag="ul"
            :list="categoriesIncome"
            @update="sortCategories(categoriesIncome)"
            class="menu"
          >
            <li
              v-for="category in categoriesIncome"
              :key="category.id"
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

          <h6 class="heading black">{{ $t("expense") }}</h6>

          <draggable
            group="categoriesExpense"
            tag="ul"
            :list="categoriesExpense"
            @update="sortCategories(categoriesExpense)"
            class="menu"
          >
            <li
              v-for="category in categoriesExpense"
              :key="category.id"
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

          <div v-if="$permissions.canAccessTransfers" class="custom-mt-24">
            <h6 class="heading black">{{ $t("other") }}</h6>

            <ul class="menu">
              <li
                v-for="category in categoriesTransfer"
                :key="category.id"
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
            </ul>
          </div>
        </div>

    </div>
    <div class="sidebar-footer">
      <ul class="menu">
        <li>
          <a :href="`${$helper.baseUrl}reports/`">
            <i class="fas fa-chart-pie"></i>
            <span>{{ $t("reports") }}</span>
          </a>
        </li>
        <li>
          <a :href="`${$helper.baseUrl}import/`">
            <i class="fas fa-file-import"></i>
            <span>{{ $t("import") }}</span>
          </a>
        </li>
        <li>
          <a :href="`${$helper.baseUrl}shop/settings/`">
            <i class="fas fa-sliders-h"></i>
            <span>Shop-Script</span>
          </a>
        </li>
      </ul>
    </div>

    <Modal v-if="openModal" @close="close">
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
import CurrencyChart from '@/components/CurrencyChart'
import SearchField from '@/components/SearchField'

export default {
  components: {
    draggable,
    Modal,
    Account,
    Category,
    CurrencyChart,
    SearchField
  },

  data () {
    return {
      openModal: false,
      currentComponentInModal: ''
    }
  },

  computed: {
    ...mapState('account', ['accounts']),
    ...mapState('category', ['categories']),
    ...mapGetters({
      currenciesInAccounts: ['account/currenciesInAccounts'],
      categoriesByType: ['category/getByType']
    }),

    categoriesIncome () {
      return this.categoriesByType('income')
    },

    categoriesExpense () {
      return this.categoriesByType('expense')
    },

    categoriesTransfer () {
      return this.categoriesByType('transfer')
    }
  },

  methods: {
    update (component) {
      this.openModal = true
      this.currentComponentInModal = component
    },

    close () {
      this.openModal = false
      this.currentComponentInModal = ''
    },

    sortAccounts () {
      const ids = this.accounts.map(e => e.id)
      this.$store.dispatch('account/sort', {
        order: ids
      })
    },

    sortCategories (list) {
      const ids = list.map(e => e.id)
      this.$store.commit('category/updateSort', list)
      this.$store.dispatch('category/sort', {
        order: ids
      })
    }
  }
}
</script>
