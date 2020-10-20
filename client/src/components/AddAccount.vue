<template>
  <div>
    <h2 class="custom-mb-32">
      {{ isModeUpdate ? $t("updateAccount") : $t("addAccount") }}
    </h2>

    <div class="fields custom-mb-32">
      <div class="field">
        <div class="name for-input">
          {{ $t("name") }}
        </div>
        <div class="value">
          <input
            v-model="model.name"
            :class="{ 'state-error': $v.model.name.$error }"
            type="text"
          />
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ $t("currency") }}
        </div>
        <div class="value">
          <div class="wa-select">
            <select
              v-model="model.currency"
              :class="{ 'state-error': $v.model.currency.$error }"
            >
              <option>RUB</option>
              <option>USD</option>
            </select>
          </div>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ $t("icon") }}
        </div>
        <div class="value">
          <input
            v-model="model.icon"
            :class="{ 'state-error': $v.model.icon.$error }"
            type="text"
            class="wide"
          />
          <p class="hint" v-if="!$v.model.icon.url">Accepts only URLs</p>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ $t("desc") }}
        </div>
        <div class="value">
          <textarea
            v-model="model.description"
            class="wide" rows="4" style="resize:none;height:auto;"
            :placeholder="$t('optional')"
          ></textarea>
        </div>
      </div>
    </div>

    <div class="flexbox">
      <div class="flexbox space-1rem wide">
        <button @click="submit" class="button purple">
          {{ isModeUpdate ? $t("update") : $t("add") }}
        </button>
        <button @click="close" class="button light-gray">
          {{ $t("cancel") }}
        </button>
      </div>
      <button v-if="isModeUpdate" @click="remove" class="button red">
        {{ $t("delete") }}
      </button>
    </div>
  </div>
</template>

<script>
import { required, url } from 'vuelidate/lib/validators'
export default {
  props: {
    editedItem: {
      type: Object
    }
  },

  data () {
    return {
      model: {
        id: null,
        name: '',
        currency: '',
        icon: '',
        description: ''
      }
    }
  },

  validations: {
    model: {
      name: {
        required
      },
      currency: {
        required
      },
      icon: {
        url
      }
    }
  },

  computed: {
    isModeUpdate () {
      return this.editedItem
    }
  },

  created () {
    if (this.editedItem) {
      for (const prop in this.model) {
        this.model[prop] = this.editedItem[prop] || this.model[prop]
      }
    }
  },

  methods: {
    submit () {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        this.$store
          .dispatch('account/update', this.model)
          .then(() => {
            this.$noty.success('Аккаунт успешно обновлен')
            this.$parent.$emit('close')
          })
          .catch(() => {
            this.$noty.error('Oops, something went wrong!')
          })
      }
    },

    remove () {
      this.$store
        .dispatch('account/delete', this.model.id)
        .then(() => {
          this.$noty.success('Аккаунт успешно удален')
          this.$parent.$emit('close')
        })
        .catch(() => {
          this.$noty.error('Oops, something went wrong!')
        })
    },

    close () {
      this.$parent.$emit('close')
    }
  }
}
</script>
