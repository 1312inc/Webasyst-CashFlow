<template>
  <div class="sidebar flexbox width-16rem" style="z-index:50;">

    <div class="sidebar-header">
      <SearchField />

      <ul class="menu custom-my-0">
        <li>
          <router-link to="/upnext" class="flexbox middle bold">{{ $t('upnext') }}</router-link>
        </li>
      </ul>
    </div>

    <div class="sidebar-body">
        <!-- Widgets charts block -->
        <SidebarCurrencyWidgets />

        <!-- Accounts list block -->
        <div v-if="accounts.length" class="custom-mt-24">
          <h6 class="heading">
            <span>{{ $t("accounts") }}</span>
            <a @click="update('Account')" class="count">
              <i class="fas fa-plus-circle"></i>
            </a>
          </h6>
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
                      account.stat.summaryShorten
                    }&nbsp;${$helper.currencySignByCode(account.currency)}`
                  "
                ></span>
              </router-link>
            </li>
          </draggable>

        </div>

        <!-- Categories list block -->
        <div v-if="categories.length" class="custom-mt-24">
          <h6 class="heading">
            <span>{{ $t("income") }}</span>
            <a @click="update('Category')" class="count">
              <i class="fas fa-plus-circle"></i>
            </a>
          </h6>

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

          <h6 class="heading">
            <span>{{ $t("expense") }}</span>
            <a @click="update('Category')" class="count">
              <i class="fas fa-plus-circle"></i>
            </a>
          </h6>

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
import SearchField from '@/components/SearchField'
import SidebarCurrencyWidgets from '@/components/SidebarCurrencyWidgets'
import utils from '@/mixins/utilsMixin.js'
export default {
  mixins: [utils],

  components: {
    draggable,
    Modal,
    Account,
    Category,
    SearchField,
    SidebarCurrencyWidgets
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
      }).catch(e => {
        this.handleApiError(e)
      })
    },

    sortCategories (list) {
      const ids = list.map(e => e.id)
      this.$store.commit('category/updateSort', list)
      this.$store.dispatch('category/sort', {
        order: ids
      }).catch(e => {
        this.handleApiError(e)
      })
    }
  }
}
</script>
