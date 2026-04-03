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
      <div class="c-sidebar-sticky-header custom-pr-12">
        <div class="flexbox">
          <div class="c-sidebar-search wide">
            <SearchField />
          </div>
          <CircleButtonsStack
            class="c-sidebar-stack"
            @click="onCircleButtonsClick"
          />
        </div>
      </div>
      <div class="c-sidebar-stickyDummy" />

      <Bricks />

      <!-- Widgets charts block -->
      <SidebarCurrencyWidgets />

      <!-- Accounts list block -->
      <SidebarHeading updating-entity-name="Account">
        {{ $t("accounts") }}
      </SidebarHeading>
      <SortableList
        :items="accounts"
        sorting-target="account"
        :group="{name: 'accounts', pull: false}"
      >
        <SortableItemAccount
          v-for="account in accounts"
          :key="account.id"
          :account="account"
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
import CircleButtonsStack from '@/components/Buttons/CircleButtonsStack'

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
    Toggler,
    CircleButtonsStack
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
    const observer = new IntersectionObserver(
      ([entry]) => {
        document.querySelector('.c-sidebar-sticky-header').classList.toggle('is-sticky', !entry.isIntersecting)
      },
      { threshold: 1, rootMargin: '-1px 0px 0px 0px' }
    )

    observer.observe(document.querySelector('.c-sidebar-stickyDummy'))
  },

  methods: {
    onCircleButtonsClick (type) {
      this.$eventBus.emit('openAddTransactionModal', {
        type
      })
    },
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

.c-sidebar-sticky-header {
  position: sticky;
  top: 0;
  z-index: 99999;
  background: var(--background-color);
}

.c-sidebar-sticky-header.is-sticky {
  box-shadow: 0 .1rem 1rem .1rem rgba(0, 0, 0, 0.1);
}

.c-sidebar-stack {
  transform: translateY(12px);
}

.c-sidebar-sticky-header:has(.c-sidebar-stack:hover) .c-sidebar-search {
  transition: all .2s;
  opacity: 0;
}
</style>
