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
        <button @click="submit('category')" class="button purple">
          {{ isModeUpdate ? $t("update") : $t("add") }}
        </button>
        <button @click="close" class="button light-gray">
          {{ $t("cancel") }}
        </button>
      </div>
      <button v-if="isModeUpdate" @click="remove('category')" class="button red">
        {{ $t("delete") }}
      </button>
    </div>
  </div>
</template>

<script>
import { required } from 'vuelidate/lib/validators'
import updateEntityMixin from '@/mixins/updateEntityMixin'
import CategoryColors from '@/components/CategoryColors'
export default {
  mixins: [updateEntityMixin],

  props: ['editedItem'],

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

  methods: {
    selectColor (color) {
      this.model.color = color
    }
  }
}
</script>
