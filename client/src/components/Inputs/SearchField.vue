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
        v-if="results.length"
        :style="
          `top:${$refs.input.offsetTop + $refs.input.offsetHeight}px;width:${
            $refs.input.offsetWidth
          }px;`
        "
        class="c-autocomplete-menu menu"
      >
        <li
          @mousedown="select(i)"
          v-for="(item, i) in results"
          :key="item.id"
          :class="{ active: i === aciveMenuIndex }"
          class="c-autocomplete-menu__item"
        >
          <a href="javascript:void(0);">
            <i v-if="!item.photo_url_absolute" class="fas fa-search"></i>
            <span v-else class="icon">
              <img :src="item.photo_url_absolute" class="userpic" alt="" />
            </span>
            <span>{{ item.name }}</span>
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
      results: [],
      aciveMenuIndex: null
    }
  },

  watch: {
    $route (val) {
      this.resetAutocomplete()

      if (val.name !== 'Search') {
        this.queryText = ''
      }
    },
    queryText: debounce(function (val) {
      this.input(val)
    }, 500)
  },

  methods: {
    submit () {
      if (this.aciveMenuIndex > 0) {
        this.$router.push({
          name: 'Contact',
          params: { id: this.results[this.aciveMenuIndex].id },
          query: {
            name: this.results[this.aciveMenuIndex].name
          }
        })
        return
      }
      if (this.queryText && this.queryText !== this.$route.query.text) {
        this.$router.push({ name: 'Search', query: { text: this.queryText } })
      }
    },

    input (val) {
      if (!val || val === '0') {
        this.resetAutocomplete()
        return
      }
      api
        .get('cash.system.searchContacts', {
          params: {
            term: val
          }
        })
        .then(({ data }) => {
          this.results = [{ id: -1, name: val }, ...data]
        })
        .catch(e => {})
    },

    resetAutocomplete () {
      this.results = []
      this.aciveMenuIndex = null
    },

    select (index) {
      this.aciveMenuIndex = index
      this.submit()
    },

    up () {
      if (this.results.length) {
        if (this.aciveMenuIndex === null) {
          this.aciveMenuIndex = this.results.length - 1
        } else if (this.aciveMenuIndex > 0) {
          this.aciveMenuIndex = this.aciveMenuIndex - 1
        } else {
          this.aciveMenuIndex = null
        }
      }
    },

    down () {
      if (this.results.length) {
        if (this.aciveMenuIndex === null) {
          this.aciveMenuIndex = 0
        } else if (this.aciveMenuIndex < this.results.length - 1) {
          this.aciveMenuIndex = this.aciveMenuIndex + 1
        } else {
          this.aciveMenuIndex = null
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
