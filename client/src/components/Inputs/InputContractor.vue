<template>
  <div style="position: relative">
    <div
      v-if="isNewContractorMode"
      class="flexbox middle"
    >
      <span class="icon gray"><i class="fas fa-plus" /></span>
      <input
        v-model.trim="inputValue"
        type="text"
        class="width-100 custom-mx-8"
      >
      <a
        class="icon gray"
        style="flex: none;"
        @click.prevent="() => { inputValue = ''; isNewContractorMode = false }"
      ><i class="fas fa-times" /></a>
    </div>

    <div
      v-else
      class="flexbox middle space-8"
    >
      <div class="state-with-inner-icon left width-100">
        <input
          ref="inputRef"
          v-model.trim="inputValue"
          type="text"
          autocomplete="off"
          class="full-width custom-mr-0"
          @input="onInput"
          @keydown.up.prevent="up"
          @keydown.down.prevent="down"
          @keydown.enter="() => { if (activeMenuIndex === response.length) { reset(); isNewContractorMode = true } else { select(response[activeMenuIndex]) } }"
          @blur="reset"
        >
        <i
          v-if="selectedContractor?.photo_url_absolute"
          :style="`background-image: url(${selectedContractor.photo_url_absolute}); opacity: 1;`"
          class="icon userpic"
        />
        <span
          v-else
          class="icon"
        ><i class="fas fa-user-plus" /></span>
      </div>
      <a
        class="icon gray"
        @click.prevent="inputValue = ''"
      ><i class="fas fa-times" /></a>
    </div>

    <div class="hint custom-mt-8">
      <div v-if="isNewContractorMode">
        {{ $t('newContact') }}
      </div>

      <template v-else>
        <i18n
          v-if="inputValue === ''"
          path="linkContact.main"
        >
          <a
            v-if="$props.createNewContractor"
            @click.prevent="isNewContractorMode = true"
          >{{ $t('linkContact.link')
          }}</a>
        </i18n>

        <!-- <div v-else-if="isFetching">
          loading
        </div> -->

        <div v-else-if="selectedContractor">
          {{ $t('selectedContact') }}
        </div>

        <i18n
          v-else-if="!selectedContractor && !response.length"
          path="noContact.main"
        >
          <template v-if="$props.createNewContractor">
            <span>. <a @click.prevent="isNewContractorMode = true">{{
              $t('noContact.link') }}</a></span>
          </template>
        </i18n>

        <div v-else>
          &nbsp;
        </div>
      </template>

      <!-- {{
        inputValue.trim() === ""
          ? $t("linkContact")
          : isNewContractorMode
          ? $t("newContact")
          : isNotFound
          ? $t("notFound")
          : $t("linkContact")
      }} -->
    </div>

    <ul
      v-if="response.length"
      ref="menu"
      :style="`top:${$refs.inputRef.offsetTop + $refs.inputRef.offsetHeight + 1}px;width:${$refs.inputRef.offsetWidth
      }px;`
      "
      tabindex="0"
      class="c-autocomplete-menu custom-m-0 custom-p-0 z-20"
    >
      <li
        v-for="(item, i) in response"
        :key="item.id"
        :class="{ active: i === activeMenuIndex }"
        tabindex="-1"
        class="c-autocomplete-menu__item"
        @mousedown="select(item)"
      >
        <div class="small flexbox middle space-8 custom-py-8 custom-px-12">
          <i
            class="icon userpic"
            :style="`background-image: url(${item.photo_url_absolute}); opacity: 1;`
            "
          />
          <span>{{ item.name }}</span>
        </div>
      </li>
      <li
        :class="{ active: activeMenuIndex === response.length }"
        class="c-autocomplete-menu__item"
        @mousedown="() => { reset(); isNewContractorMode = true }"
      >
        <div class="small flexbox middle space-8 custom-py-8 custom-px-12">
          {{ $t('createNewContact') }}
        </div>
      </li>
    </ul>
  </div>
</template>

<script>
import api from '@/plugins/api'
import { debounce } from '@/utils/debounce'

export default {
  props: {
    defaultContractor: {
      type: Object,
      default: null
    },

    createNewContractor: {
      type: Boolean,
      default: true
    },

    defaultRequest: {
      type: String,
      default: ''
    }

    // focus: {
    //   type: Boolean,
    //   default: false
    // }
  },

  data () {
    return {
      inputValue: this.defaultContractor?.name || '',
      activeMenuIndex: null,
      isNewContractorMode: false,
      selectedContractor: this.defaultContractor ? { ...this.defaultContractor, photo_url_absolute: this.defaultContractor.userpic } : null,
      response: []
      // isFetching: false
    }
  },

  watch: {
    selectedContractor: {
      handler (contractor) {
        this.$emit('changeContractor', contractor ? contractor.id : (this.defaultContractor?.id ?? null))
      },
      deep: true
    },
    inputValue (value) {
      if (this.isNewContractorMode) {
        this.$emit('newContractor', value)
      }
    },
    isNewContractorMode (is) {
      this.selectedContractor = null
      this.$emit('newContractor', is ? this.inputValue || null : null)
      this.$emit('changeContractor', is ? null : this.defaultContractor?.id ?? null)
    },
    response (contractors) {
      const target = contractors.find(c => c.name === this.inputValue)
      if (target) this.select(target)
    }
  },

  // mounted () {
  //   if (this.focus) {
  //     this.$refs.inputRef.focus()
  //   }
  // },

  methods: {
    onInput: debounce(function () {
      this.reset()
      this.selectedContractor = null

      // make search request
      // this.isFetching = true
      const term = this.inputValue || this.defaultRequest
      if (!term) return
      api
        .get('cash.contact.search', {
          params: {
            term,
            limit: 10
          }
        })
        .then(({ data }) => {
          // this.isFetching = false
          this.response = data
        })
        .catch(e => { })
    }, 400),

    select (contractor) {
      this.selectedContractor = contractor
      this.inputValue = contractor.name
      this.reset()
    },

    reset () {
      this.response = []
      this.activeMenuIndex = null
    },

    up () {
      if (this.$refs.menu) {
        if (this.activeMenuIndex === null) {
          this.activeMenuIndex = this.response.length
        } else if (this.activeMenuIndex > 0) {
          this.activeMenuIndex = this.activeMenuIndex - 1
        } else {
          this.activeMenuIndex = null
        }
      }
    },

    down () {
      if (this.$refs.menu) {
        if (this.activeMenuIndex === null) {
          this.activeMenuIndex = 0
        } else if (this.activeMenuIndex < this.response.length) {
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
input {
  transition: none;
}

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
