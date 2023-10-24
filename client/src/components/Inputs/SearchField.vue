<template>
  <div class="box custom-mt-4 custom-mb-8">
    <form
      class="state-with-inner-icon left width-100"
      style="position: relative;"
      @submit.prevent="submit"
    >
      <input
        ref="input"
        v-model.trim="queryText"
        :placeholder="$t('search.label')"
        class="width-100 solid"
        type="search"
        name="query"
        @keyup.up="up"
        @keyup.down="down"
      >
      <ul
        v-if="results.length > 1"
        :style="`top:${$refs.input.offsetTop + $refs.input.offsetHeight}px;width:${$refs.input.offsetWidth
        }px;`
        "
        class="c-autocomplete-menu menu"
      >
        <li
          v-for="(item, i) in results"
          :key="i"
          :class="{ active: i === activeMenuIndex }"
          class="c-autocomplete-menu__item"
          @mousedown="activeMenuIndex = i; submit()"
        >
          <a @click.prevent="">
            <span
              v-if="item.entity.photo_url_absolute"
              class="icon"
            >
              <img
                :src="item.entity.photo_url_absolute"
                :class="{
                  userpic: item.routeName !== 'Order'
                }"
                alt=""
              >
            </span>
            <span
              v-else-if="item.entity.glyph"
              class="icon"
            >
              <i
                :class="item.entity.glyph"
                :style="`color: ${item.entity.color};`"
              />
            </span>
            <span
              v-else-if="item.entity.color"
              class="icon"
            >
              <i
                class="rounded"
                :style="`background-color: ${item.entity.color};`"
              />
            </span>
            <i
              v-else
              class="fas fa-search"
            />
            <span>{{ item.entity.name ?? queryText }}</span>
          </a>
        </li>
      </ul>
      <button class="icon">
        <i class="fas fa-search" />
      </button>
    </form>
  </div>
</template>

<script>
import api from '@/plugins/api'
import { debounce } from '@/utils/debounce'
export default {
  data () {
    return {
      queryText: '',
      activeMenuIndex: null,
      results: []
    }
  },
  watch: {
    $route: 'resetAutocomplete',
    queryText: debounce(function (val) {
      this.input(val)
    }, 500)
  },
  methods: {
    submit () {
      this.$router
        .push({
          name: this.results[this.activeMenuIndex].routeName,
   ***REMOVED***this.results[this.activeMenuIndex].routeParams
        })
        .catch(() => { })
    },
    async input (searchString) {
      if (!searchString || searchString === '0') {
        this.resetAutocomplete()
        return
      }
      const searchedContacts = await this.searchContacts(searchString)
      const searchedCategories = this.searchCategories(searchString)

      let searchedOrder
      if (window.appState.shopscriptInstalled) {
        searchedOrder = {
          routeName: 'Order',
          routeParams: { params: { id: this.queryText } },
          entity: {
            name: `${this.$t('Order')} ${this.queryText}`,
            photo_url_absolute: `${window.appState.baseWAUrl}wa-apps/cash/img/shop.svg`
          }
        }
      }

      this.results = [
        this.makeSearchListObjectFromEntity(searchString, 'Search'),
 ***REMOVED***searchedCategories,
 ***REMOVED***searchedContacts,
        searchedOrder
      ]
    },
    searchCategories (searchString) {
      return this.$store.state.category.categories
        .filter(c => c.name.toLowerCase().includes(searchString.toLowerCase()))
        .map(cat => this.makeSearchListObjectFromEntity(cat, 'Category'))
    },
    async searchContacts (searchString) {
      const { data } = await api.get('cash.contact.search', {
        params: {
          term: searchString
        }
      })
      return data.map(e => this.makeSearchListObjectFromEntity(e, 'Contact'))
    },
    makeSearchListObjectFromEntity (entity, entityType) {
      return {
        routeName: entityType,
        routeParams: entityType !== 'Search'
          ? { params: { id: entity.id } }
          : { query: { text: entity } },
        entity
      }
    },
    resetAutocomplete () {
      this.results = []
      this.activeMenuIndex = null
      if (this.$route.name !== 'Search') {
        this.queryText = ''
      }
    },
    up () {
      if (this.results.length) {
        if (this.activeMenuIndex === null) {
          this.activeMenuIndex = this.results.length - 1
        } else if (this.activeMenuIndex > 0) {
          this.activeMenuIndex = this.activeMenuIndex - 1
        } else {
          this.activeMenuIndex = null
        }
      }
    },
    down () {
      if (this.results.length) {
        if (this.activeMenuIndex === null) {
          this.activeMenuIndex = 0
        } else if (this.activeMenuIndex < this.results.length - 1) {
          this.activeMenuIndex = this.activeMenuIndex + 1
        } else {
          this.activeMenuIndex = null
        }
      }
    }
  }
}
</script>

<style lang="scss" scoped>
.c-autocomplete-menu {
  position: absolute;
  background: var(--background-color-blank) !important;
  box-shadow: 0 0.5rem 1rem -0.5rem var(--dialog-shadow-color);
  list-style: none;
  padding: 0;
  margin: 0;
  z-index: 99;

  &__item {
    cursor: pointer;

    &:focus,
    &:hover,
    &.active {
      outline: none;
      background-color: var(--accent-color);
      color: white;
    }
  }
}
</style>
