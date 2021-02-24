<template>
  <div>
    <div class="state-with-inner-icon left">
      <input
        :value="inputLabel"
        @input="input"
        @keyup.up="up"
        @keyup.down="down"
        @keyup.enter="select(aciveMenuIndex)"
        @blur="reset"
        ref="input"
        type="text"
        autocomplete="off"
      />
      <span v-if="!computedPhoto" class="icon"
        ><i class="fas fa-user"></i
      ></span>
      <i
        v-else
        :style="`background-image: url(${computedPhoto});opacity: 1;`"
        class="icon userpic"
      ></i>
    </div>

    <ul
      v-if="response.length"
      ref="menu"
      :style="`width:${$refs.input.offsetWidth}px;`"
      tabindex="0"
      class="c-autocomplete-menu custom-m-0 custom-p-0 z-20"
    >
      <li
        @mousedown="select(i)"
        v-for="(item, i) in response"
        :key="item.id"
        :class="{ active: i === aciveMenuIndex }"
        tabindex="-1"
        class="c-autocomplete-menu__item"
      >
        <div class="small flexbox middle space-8 custom-py-8 custom-px-12">
          <i
            class="icon userpic"
            :style="`background-image: url(${item.photo_url_absolute});opacity: 1;`"
          ></i>
          <span>{{ item.name }}</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import api from '@/plugins/api'
export default {
  props: ['defaultContractor'],

  data () {
    return {
      inputValue: '',
      photo: '',
      aciveMenuIndex: null,
      response: []
    }
  },

  computed: {
    inputLabel () {
      return this.response[this.aciveMenuIndex]?.name || this.inputValue
    },
    computedPhoto () {
      return (
        this.response[this.aciveMenuIndex]?.photo_url_absolute || this.photo
      )
    }
  },

  created () {
    this.inputValue = this.defaultContractor?.name || ''
    this.photo = this.defaultContractor?.userpic || ''
  },

  methods: {
    input ({ target }) {
      this.inputValue = target.value
      this.photo = ''
      this.$emit('newContractor', target.value.trim())

      // prevent search request if empty string
      if (!target.value.trim()) {
        this.reset()
        return false
      }

      // make search request
      api
        .get('cash.system.searchContacts', {
          params: {
            term: target.value
          }
        })
        .then(({ data }) => {
          this.reset()
          this.response = data
          // check if the response has an exact search string
          const i = data.findIndex(e => e.name === this.inputValue)
          if (i > -1) {
            this.select(i)
          }
        })
        .catch(e => {})
    },

    select (index) {
      if (index !== null) {
        this.$emit('changeContractor', this.response[index].id)
        this.inputValue = this.response[index].name
        this.photo = this.response[index].photo_url_absolute
        this.response = []
        this.aciveMenuIndex = null
      }
    },

    reset () {
      this.response = []
      this.aciveMenuIndex = null
    },

    up () {
      if (this.$refs.menu) {
        if (this.aciveMenuIndex === null) {
          this.aciveMenuIndex = this.response.length - 1
        } else if (this.aciveMenuIndex > 0) {
          this.aciveMenuIndex = this.aciveMenuIndex - 1
        } else {
          this.aciveMenuIndex = null
        }
      }
    },

    down () {
      if (this.$refs.menu) {
        if (this.aciveMenuIndex === null) {
          this.aciveMenuIndex = 0
        } else if (this.aciveMenuIndex < this.response.length - 1) {
          this.aciveMenuIndex = this.aciveMenuIndex + 1
        } else {
          this.aciveMenuIndex = null
        }
      }
    }
  }
}
</script>

<style lang="scss">
.c-autocomplete-menu {
  background: var(--background-color-blank) !important;
  position: absolute;
  list-style: none;
  box-shadow: 0 0.5rem 1rem -0.5rem var(--dialog-shadow-color);

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
