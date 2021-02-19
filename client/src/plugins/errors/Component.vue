<template>
  <div v-if="errors.length" class="alert-fixed-box">
    <div v-for="(error, i) in errors" :key="i" class="alert danger">
      <div class="flexbox space-8 full-width">
        <div class="alert__icon">
          <span class="icon"><i class="fas fa-skull"></i></span>
        </div>
        <div class="alert__content">
          <div class="custom-mb-4">{{ $t(error.title) }}</div>
          <div class="opacity-70">{{ error.message }}</div>
        </div>
        <div class="alert__close">
          <a href="#" @click.prevent="remove(i)" class="alert-close"
            ><i class="fas fa-times"></i
          ></a>
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
  }
}
</style>
