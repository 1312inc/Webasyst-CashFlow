<template>
  <div class="sidebar flexbox width-16rem" style="z-index: 50">
    <div class="sidebar-header">
      <SearchField />

      <ul class="menu custom-my-0">
        <li>
          <router-link to="/upnext" class="flexbox middle bold">{{
            $t("upnext")
          }}</router-link>
        </li>
      </ul>
    </div>

    <div class="sidebar-body">
      <!-- Widgets charts block -->
      <SidebarCurrencyWidgets />

      <!-- Accounts list block -->
      <h6 class="heading">
        <span>{{ $t("accounts") }}</span>
        <a @click="update('Account')" class="count">
          <i class="fas fa-plus-circle"></i>
        </a>
      </h6>
      <SidebarAccountList>
        <SidebarAccountListItem
          v-for="account in accounts"
          :key="account.id"
          :account="account"
        />
      </SidebarAccountList>

      <!-- Categories list block -->
      <h6 class="heading custom-mt-24">
        <span>{{ $t("income") }}</span>
        <a @click="update('Category')" class="count">
          <i class="fas fa-plus-circle"></i>
        </a>
      </h6>
      <SidebarCategoryList :categories="categoriesIncome">
        <SidebarCategoryListItem
          v-for="category in categoriesIncome"
          :key="category.id"
          :category="category"
        />
      </SidebarCategoryList>

      <h6 class="heading">
        <span>{{ $t("expense") }}</span>
        <a @click="update('Category')" class="count">
          <i class="fas fa-plus-circle"></i>
        </a>
      </h6>
      <SidebarCategoryList :categories="categoriesExpense">
        <SidebarCategoryListItem
          v-for="category in categoriesExpense"
          :key="category.id"
          :category="category"
        />
      </SidebarCategoryList>

      <div v-if="$permissions.canAccessTransfers" class="custom-mt-24">
        <h6 class="heading black">{{ $t("other") }}</h6>

        <ul class="menu">
          <li v-for="category in categoriesTransfer" :key="category.id">
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

    <div class="sidebar-footer shadowed">
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

    <portal>
      <Modal v-if="openModal" @close="close">
        <component :is="currentComponentInModal"></component>
      </Modal>
    </portal>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import Modal from '@/components/Modal'
import SidebarAccountList from './SidebarAccountList'
import SidebarAccountListItem from './SidebarAccountListItem'
import SidebarCategoryList from './SidebarCategoryList'
import SidebarCategoryListItem from './SidebarCategoryListItem'
import Account from '@/components/AddAccount'
import Category from '@/components/AddCategory'
import SearchField from '@/components/SearchField'
import SidebarCurrencyWidgets from './SidebarCurrencyWidgets'
export default {
  components: {
    SidebarAccountList,
    SidebarAccountListItem,
    SidebarCategoryList,
    SidebarCategoryListItem,
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
    }
  }
}
</script>
