<!-- eslint-disable vue/multi-word-component-names -->
<template>
  <Fragment>
    <nav class="sidebar-mobile-toggle">
      <div class="box align-center">
        <a
          href="#"
          @click.prevent="mobileMenuOpen = !mobileMenuOpen"
        >
          <i class="fas fa-bars" />
          {{
            $t("navigation", {
              action: mobileMenuOpen ? $t("hide") : $t("show"),
            })
          }}</a>
      </div>
    </nav>
    <div
      ref="sidebarBody"
      class="sidebar-body hide-scrollbar"
    >
      <SearchField />

      <Bricks />

      <!-- Widgets charts block -->
      <SidebarCurrencyWidgets />

      <!-- Accounts list block -->
      <SidebarHeading updating-entity-name="Account">
        {{ $t("accounts") }}
      </SidebarHeading>
      <SortableList
        :items="accountsList"
        sorting-target="account"
        :group="{name: 'accounts', pull: false}"
      >
        <SortableItemAccount
          v-for="account in accountsList"
          :key="account.id"
          :account="account"
        />
      </SortableList>

      <div
        v-if="accountsSandbox.length && !showSandbox"
        class="flexbox middle custom-mx-12"
        style="cursor: pointer;"
        @click.prevent="showSandbox = !showSandbox"
      >
        <span class="small custom-ml-4 custom-mr-12 gray"><i class="fas fa-chevron-down"></i></span>
        <span class="small wide gray">{{ $t("hiddenAccounts") }}</span>
        <span class="smaller badge light-gray">{{ accountsSandbox.length }}</span>
      </div>

      <Toggler>
        <template #categories>
          <SidebarCategories />
        </template>
        <template #contacts>
          <ContactsList />
        </template>
      </Toggler>

      <div
        v-if="$permissions.canAccessTransfers"
        class="custom-mt-24"
      >
        <h6 class="heading">
          <span>{{ $t("other") }}</span>
        </h6>

        <ul class="menu">
          <li
            v-for="category in categoriesTransfer"
            :key="category.id"
          >
            <router-link
              :to="`/category/${category.id}`"
              class="flexbox middle"
            >
              <span class="icon">
                <i class="fas fa-exchange-alt" />
              </span>
              <span>{{ category.name }}</span>
            </router-link>
          </li>
        </ul>
      </div>
    </div>

    <div
      ref="sidebarFooter"
      class="sidebar-footer shadowed"
    >
      <SidebarFooter />
    </div>
  </Fragment>
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
      mobileMenuOpen: false,
      showSandbox: false
    }
  },

  computed: {
    ...mapState('account', ['accounts']),

    ...mapGetters({
      categoriesByType: ['category/getByType']
    }),

    categoriesTransfer () {
      return this.categoriesByType('transfer')
    },

    accountsSandbox () {
      return this.accounts.filter(account => account.is_imaginary === -1)
    },

    accountWithoutSandbox () {
      return this.accounts.filter(account => account.is_imaginary !== -1)
    },

    accountsList () {
      return this.showSandbox ? this.accounts : this.accountWithoutSandbox
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
