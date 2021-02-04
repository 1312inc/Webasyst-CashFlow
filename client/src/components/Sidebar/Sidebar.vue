<template>
  <div class="sidebar flexbox width-16rem" style="z-index: 50">
    <div class="sidebar-header">
      <SearchField />

      <ul class="menu custom-my-0">
        <li :class="{'selected': $route.path === '/upnext'}">
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
      <SidebarHeading updatingEntityName="Account">
        {{ $t("accounts") }}
      </SidebarHeading>
      <SidebarAccountList>
        <SidebarAccountListItem
          v-for="account in accounts"
          :key="account.id"
          :account="account"
        />
      </SidebarAccountList>

      <!-- Categories list block -->
      <SidebarHeading class="custom-mt-24" updatingEntityName="Category">
        {{ $t("income") }}
      </SidebarHeading>
      <SidebarCategoryList :categories="categoriesIncome">
        <SidebarCategoryListItem
          v-for="category in categoriesIncome"
          :key="category.id"
          :category="category"
        />
      </SidebarCategoryList>

      <SidebarHeading updatingEntityName="Category">
        {{ $t("expense") }}
      </SidebarHeading>
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

    <SidebarFooter />
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import SidebarAccountList from './SidebarAccountList'
import SidebarAccountListItem from './SidebarAccountListItem'
import SidebarCategoryList from './SidebarCategoryList'
import SidebarCategoryListItem from './SidebarCategoryListItem'
import SidebarHeading from './SidebarHeading'
import SidebarFooter from './SidebarFooter'
import SearchField from '@/components/SearchField'
import SidebarCurrencyWidgets from './SidebarCurrencyWidgets'

export default {
  components: {
    SidebarAccountList,
    SidebarAccountListItem,
    SidebarCategoryList,
    SidebarCategoryListItem,
    SidebarHeading,
    SidebarFooter,
    SearchField,
    SidebarCurrencyWidgets
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
  }
}
</script>
