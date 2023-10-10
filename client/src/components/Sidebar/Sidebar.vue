<template>
  <div ref="sidebar" class="sidebar flexbox hide-scrollbar width-16rem mobile-friendly z-50">
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
    <div ref="sidebarBody" class="sidebar-body hide-scrollbar">
      <SearchField />
      <Bricks />

      <!-- Widgets charts block -->
      <SidebarCurrencyWidgets />

      <!-- Accounts list block -->
      <SidebarHeading updatingEntityName="Account">
        {{ $t("accounts") }}
      </SidebarHeading>
      <SortableList
        :items="accounts"
        sortingTarget="account"
        :group="{name: 'accounts', pull: false}">
        <SortableItemAccount
          v-for="account in accounts"
          :key="account.id"
          :account="account"
        />
      </SortableList>

      <Toggler>
        <template v-slot:categories>
          <SidebarCategories />
        </template>
        <template v-slot:contacts>
          <ContactsList />
        </template>
      </Toggler>

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

    <div ref="sidebarFooter" class="sidebar-footer shadowed">
      <SidebarFooter />
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import SortableList from './Sortable/SortableList'
import SortableItemAccount from './Sortable/SortableItemAccount'
import SidebarHeading from './SidebarHeading'
import SidebarCategories from './SidebarCategories'
import SidebarFooter from './SidebarFooter'
import SearchField from '@/components/Inputs/SearchField'
import SidebarCurrencyWidgets from './SidebarCurrencyWidgets'
import ContactsList from '@/components/ContactsList/ContactsList'
import Toggler from '@/components/Toggler/Toggler'
import Bricks from '@/components/Bricks/Bricks'

export default {
  components: {
    SortableList,
    SortableItemAccount,
    SidebarCategories,
    SidebarHeading,
    SidebarFooter,
    SearchField,
    SidebarCurrencyWidgets,
    Bricks,
    ContactsList,
    Toggler
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

    categoriesTransfer () {
      return this.categoriesByType('transfer')
    }
  },

  watch: {
    $route () {
      this.mobileMenuOpen = false
    },
    mobileMenuOpen (val) {
      val ? this.menuOpen() : this.menuClose()
    }
  },

  mounted () {
    // Remove top margin if no WA header
    if (!this.$helper.isHeader()) {
      this.$refs.sidebar.style.top = 0
      this.$refs.sidebar.style.height = '100vh'
    }
  },

  methods: {
    menuOpen () {
      ['Body', 'Footer'].forEach(h => {
        this.$refs[`sidebar${h}`].style['max-height'] =
          this.$refs[`sidebar${h}`].scrollHeight + 'px'
      })
    },
    menuClose () {
      ['Body', 'Footer'].forEach(h => {
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
