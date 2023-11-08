<template>
  <div
    v-if="errors.length"
    class="alert-fixed-box"
  >
    <div
      v-for="(error, i) in errors"
      :key="i"
      :class="{
        warning: error.title === 'error.api',
        danger: error.title === 'error.http',
      }"
      class="alert"
    >
      <div class="flexbox space-8 full-width">
        <div class="alert__icon">
          <span class="icon"><i
            :class="{
              'fa-exclamation-triangle': error.title === 'error.api',
              'fa-skull': error.title === 'error.http',
            }"
            class="fas"
          /></span>
        </div>
        <div class="alert__content">
          <div class="custom-mb-8">
            {{ error.method }}
          </div>
          <div class="opacity-70">
            {{ error.message }}
          </div>
        </div>
        <div class="alert__close">
          <a
            href="#"
            class="alert-close"
            @click.prevent="remove(i)"
          ><i class="fas fa-times" /></a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import store from '../../store'
import { i18n } from '../../plugins/locale'
export default {
  store,
  i18n,
  computed: {
    errors () {
      return this.$store.state.errors.errors
    }
  },

  methods: {
    remove (index) {
      this.$store.commit('errors/delete', index)
    }
  }
}
</script>

<style lang="scss">
.alert {
  &__icon,
  &__close {
    width: 1rem;
  }

  &__content {
    flex-shrink: 1;
    max-height: 320px;
    overflow: hidden;
  }
}
</style>
