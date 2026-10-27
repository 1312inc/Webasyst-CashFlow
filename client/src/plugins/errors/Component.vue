<template>
  <div>
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
            <div v-if="error.data">
              <button @click="viewResponse(error.data)">
                {{ $t('error.viewResponse') }}
              </button>
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

    <Modal
      v-if="response !== null"
      @close="response = null"
    >
      <div
        class="dialog-body"
        style="width: 70%;"
      >
        <div class="dialog-content">
          <pre class="small c-error-response">{{ responseText }}</pre>
        </div>
        <div class="dialog-footer">
          <button
            type="button"
            class="button light-gray"
            @click="response = null"
          >
            {{ $t('close') }}
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import store from '../../store'
import { i18n } from '../../plugins/locale'
import Modal from '@/components/Modal'

export default {
  store,
  i18n,
  components: {
    Modal
  },
  data () {
    return {
      response: null
    }
  },
  computed: {
    errors () {
      return this.$store.state.errors.errors
    },
    responseText () {
      const data = this.response
      if (typeof data === 'string') {
        return data
      }
      try {
        return JSON.stringify(data, null, 2)
      } catch (e) {
        return String(data)
      }
    }
  },

  methods: {
    remove (index) {
      this.$store.commit('errors/delete', index)
    },

    viewResponse (data) {
      this.response = data
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

.c-error-response {
  white-space: pre-wrap;
  overflow-wrap: anywhere;
  max-height: 60vh;
  overflow: auto;
  margin: 0;
}
</style>
