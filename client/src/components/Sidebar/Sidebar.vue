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

      <!-- Widgets charts block -->
      <SidebarCurrencyWidgets />

      <Bricks />

      <!-- Accounts list block -->
      <SidebarHeading updating-entity-name="Account">
        {{ $t("accounts") }}
      </SidebarHeading>
      <SortableList
        :items="accountWithoutSandbox"
        sorting-target="account"
        :group="{name: 'accounts', pull: false}"
      >
        <SortableItemAccount
          v-for="account in accountWithoutSandbox"
          :key="account.id"
          :account="account"
        />
      </SortableList>
      <div class="heading">
        <span>Скрытые счета ({{ accountsSandbox.length }})</span>
        <a
          href="#"
          class="count action"
          @click.prevent="showSandbox = !showSandbox"
        >
          <span v-show="showSandbox">
            <i class="fas fa-chevron-up" />
          </span>
          <span v-show="!showSandbox">
            <i class="fas fa-chevron-down" />
          </span>
        </a>
      </div>
      <SortableList
        v-if="showSandbox"
        :items="accountsSandbox"
        sorting-target="sandbox"
        :group="{name: 'sandbox', pull: false}"
      >
        <SortableItemAccount
          v-for="sandbox in accountsSandbox"
          :key="sandbox.id"
          :account="sandbox"
        />
      </SortableList>

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
