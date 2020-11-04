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
              class="wide"
              :class="{ 'state-error': $v.model.currency.$error }"
            >
              <option :value="c.code" v-for="c in $store.state.system.currencies" :key="c.code">{{c.code}} – {{c.title}} ({{c.sign}})</option>
            </select>
          </div>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ $t("icon") }}
        </div>
        <div class="value">
          <div class="icon-uploader">
            <div v-if="$helper.isValidHttpUrl(model.icon)" class="flexbox middle">
              <div class="icon-uploader-image tw-rounded" :style="`background-image:url(${model.icon})`"></div>
              <div>
                <a href="#" @click.prevent="model.icon = ''" class="button nobutton smaller">Remove</a>
              </div>
            </div>
            <div v-else>
              <IconUploader @uploaded="setIcon" />
            </div>
          </div>
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
import { required } from 'vuelidate/lib/validators'
import IconUploader from '@/components/IconUploader'
export default {
  props: {
    editedItem: {
      type: Object
    }
  },

  components: {
    IconUploader
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
      }
    }
  },

  computed: {
    isModeUpdate () {
      return !!this.editedItem
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
            this.close()
          })
          .catch(() => {
            this.$noty.error('Oops, something went wrong!')
          })
      }
    },

    remove () {
      if (confirm(this.$t('deleteWarning', { type: this.$t('accounts') }))) {
        this.$store
          .dispatch('account/delete', this.model.id)
          .then(() => {
            this.$noty.success('Аккаунт успешно удален')
            this.close()
          })
          .catch(() => {
            this.$noty.error('Oops, something went wrong!')
          })
      }
    },

    close () {
      this.$parent.$emit('close')
    },

    setIcon (url) {
      this.model.icon = url
    }
  }
}
</script>

<style lang="scss">
.icon-uploader {
  height: 40px;
  position: relative;

  &-image {
    width: 40px;
    height: 40px;
    background: {
      position: center center;
      size: cover;
    }
  }
}
</style>
