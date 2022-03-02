<template>
  <div class="box custom-mt-4 custom-mb-8">
    <form
      @submit.prevent="submit"
      class="state-with-inner-icon left width-100"
      style="position: relative;"
    >
      <input
        v-model.trim="queryText"
        @keyup.up="up"
        @keyup.down="down"
        ref="input"
        :placeholder="$t('search.label')"
        class="width-100 solid"
        type="search"
        name="query"
      />
      <ul
        v-if="results.length > 1"
        :style="
          `top:${$refs.input.offsetTop + $refs.input.offsetHeight}px;width:${
            $refs.input.offsetWidth
          }px;`
        "
        class="c-autocomplete-menu menu"
      >
        <li
          v-for="(item, i) in results"
          :key="i"
          @mousedown="activeMenuIndex = i;submit()"
          :class="{ active: i === activeMenuIndex }"
          class="c-autocomplete-menu__item"
        >
          <a href="javascript:void(0);">
            <span v-if="item.entityIcon" class="icon">
              <img :src="item.entityIcon" class="userpic" alt="" />
            </span>
            <span v-else-if="item.entityGlyph" class="icon">
              <i
                :class="item.entityGlyph"
                :style="`color: ${item.entityColor};`"
              ></i>
            </span>
            <span v-else-if="item.entityColor" class="icon">
              <i
                class="rounded"
                :style="`background-color: ${item.entityColor};`"
              ></i>
            </span>
            <i v-else class="fas fa-search"></i>
            <span>{{ item.entityName }}</span>
          </a>
        </li>
      </ul>
      <button class="icon"><i class="fas fa-search"></i></button>
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
      const routeParams =
        this.activeMenuIndex > 0
          ? { params: { id: this.results[this.activeMenuIndex].entityId } }
          : { query: { text: this.queryText } }

      this.$router
        .push({
          name:
            this.activeMenuIndex > 0
              ? this.results[this.activeMenuIndex].entityType
              : 'Search',
   ***REMOVED***routeParams
        })
        .catch(() => {})
    },

    async input (searchString) {
      if (!searchString || searchString === '0') {
        this.resetAutocomplete()
        return
      }

      const searchedContacts = await this.searchContacts(searchString)
      const searchedCategories = this.searchCategories(searchString)
      this.results = [
        this.makeSearchListObjectFromEntity(searchString),
 ***REMOVED***searchedCategories,
 ***REMOVED***searchedContacts
      ]
    },

    searchCategories (searchString) {
      const cats = this.$store.state.category.categories.filter(c =>
        c.name.toLowerCase().includes(searchString.toLowerCase())
      )
      return cats.map(cat =>
        this.makeSearchListObjectFromEntity(cat, 'Category')
      )
    },

    async searchContacts (searchString) {
      const { data } = await api.get('cash.system.searchContacts', {
        params: {
          term: searchString
        }
      })
      return data.map(e => this.makeSearchListObjectFromEntity(e, 'Contact'))
    },

    makeSearchListObjectFromEntity (entity, entityType) {
      if (typeof entity === 'string') {
        return {
          entityName: entity
        }
      }
      return {
        entityId: entity.id,
        entityName: entity.name,
        entityIcon: entity.photo_url_absolute,
        entityGlyph: entity.glyph,
        entityColor: entity.color,
        entityType
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
