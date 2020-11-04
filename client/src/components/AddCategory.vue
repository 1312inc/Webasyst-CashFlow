<template>
  <div>
    <h2 class="custom-mb-32">
      {{ isModeUpdate ? $t("updateCategory") : $t("addCategory") }}
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
          {{ $t("type") }}
        </div>
        <div class="value">
          <div class="wa-select">
            <select
              v-model="model.type"
              :class="{ 'state-error': $v.model.type.$error }"
            >
              <option value="income">{{ $t("income") }}</option>
              <option value="expense">{{ $t("expense") }}</option>
            </select>
          </div>
        </div>
      </div>

      <div class="field" v-if="model.type">
        <div class="name for-input">
          {{ $t("color") }}
        </div>
        <div class="value">
          <CategoryColors
            :active="model.color"
            :type="model.type"
            @select="selectColor"
          />
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
import CategoryColors from '@/components/CategoryColors'
export default {
  props: {
    editedItem: {
      type: Object
    }
  },

  components: {
    CategoryColors
  },

  data () {
    return {
      model: {
        id: null,
        name: '',
        type: '',
        color: ''
      }
    }
  },

  validations: {
    model: {
      name: {
        required
      },
      type: {
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
          .dispatch('category/update', this.model)
          .then(() => {
            this.$noty.success('Категория успешно обновлена')
            this.close()
          })
          .catch(() => {
            this.$noty.error('Oops, something went wrong!')
          })
      }
    },

    remove () {
      if (confirm(this.$t('deleteWarning', { type: this.$t('categories') }))) {
        this.$store
          .dispatch('category/delete', this.model.id)
          .then(() => {
            this.$noty.success('Категория успешно удалена')
            this.close()
          })
          .catch((e) => {
            this.$noty.error('Oops, something went wrong!')
          })
      }
    },

    close () {
      this.$parent.$emit('close')
    },

    selectColor (color) {
      this.model.color = color
    }
  }
}
</script>
