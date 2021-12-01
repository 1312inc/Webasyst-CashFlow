<template>
  <div class="sidebar flexbox hide-scrollbar width-16rem mobile-friendly z-50">
    <nav class="sidebar-mobile-toggle">
      <div class="box align-center">
        <a @click.prevent="mobileMenuOpen = !mobileMenuOpen" href="#">
          <i class="fas fa-bars"></i>
          {{
            $t("navigation", {
              action: mobileMenuOpen ? $t("hide") : $t("show"),
            })
          }}</a
        >
      </div>
    </nav>
    <div class="sidebar-body hide-scrollbar" ref="sidebarBody">
      <SearchField />
      <Bricks />

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
      <SidebarHeading
        class="custom-mt-24"
        updatingEntityName="Category"
        type="income"
      >
        {{ $t("income") }}
      </SidebarHeading>
      <SidebarCategoryList :categories="categoriesIncome">
        <SidebarCategoryListItem
          v-for="category in categoriesIncome"
          :key="category.id"
          :category="category"
        />
      </SidebarCategoryList>

      <SidebarHeading updatingEntityName="Category" type="expense">
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
        <h6 class="heading"><span>{{ $t("other") }}</span></h6>

        <ul class="menu">
          <li v-for="category in categoriesTransfer" :key="category.id">
            <router-link
              :to="`/category/${category.id}`"
              class="flexbox middle"
            >
              <span class="icon">
                <i class="fas fa-exchange-alt"></i>
              </span>
              <span>{{ category.name }}</span>
            </router-link>
          </li>
        </ul>
      </div>
    </div>

    <div class="sidebar-footer shadowed" ref="sidebarFooter">
      <SidebarFooter />
    </div>
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
import SearchField from '@/components/Inputs/SearchField'
import SidebarCurrencyWidgets from './SidebarCurrencyWidgets'
import Bricks from '@/components/Bricks/Bricks'

export default {
  components: {
    SidebarAccountList,
    SidebarAccountListItem,
    SidebarCategoryList,
    SidebarCategoryListItem,
    SidebarHeading,
    SidebarFooter,
    SearchField,
    SidebarCurrencyWidgets,
    Bricks
  },

  data () {
    return {
      mobileMenuOpen: false
    }
  },

  computed: {
    ...mapState('account', ['accounts']),
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

  watch: {
    $route () {
      this.mobileMenuOpen = false
    },
    mobileMenuOpen: 'mobileMenuToggle'
  },

  methods: {
    mobileMenuToggle (val) {
      if (val) {
        this.menuOpen()
      } else {
        this.menuClose()
      }
    },
    menuOpen () {
      ;['Body', 'Footer'].forEach(h => {
        this.$refs[`sidebar${h}`].style['max-height'] =
          this.$refs[`sidebar${h}`].scrollHeight + 'px'
      })
    },
    menuClose () {
      ;['Body', 'Footer'].forEach(h => {
        this.$refs[`sidebar${h}`].style['max-height'] = '0px'
      })
    }
  }
}
</script>

<style lang="scss">
@media (max-width: 760px) {
  .sidebar-body,
  .sidebar-footer {
    display: block !important;
    overflow: hidden;
    transition: max-height 0.4s ease-out;
    height: auto;
    max-height: 0;
  }
}
</style>
