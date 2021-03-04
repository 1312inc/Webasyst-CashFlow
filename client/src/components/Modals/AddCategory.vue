<template>
  <div class="dialog-body">
    <div class="dialog-header">
      <h2>
        {{ isModeUpdate ? $t("updateCategory") : $t("addCategory") }}
      </h2>
    </div>
    <div class="dialog-content">
      <div class="fields">
        <div class="field">
          <div class="name for-input">
            {{ $t("name") }}
          </div>
          <div class="value">
            <input
              v-model="model.name"
              @keyup.enter="submit"
              :class="{ 'state-error': $v.model.name.$error }"
              ref="focus"
              class="bold"
              type="text"
            />
          </div>
        </div>

        <div class="field">
          <div class="name for-input">
            {{ $t("type") }}
          </div>
          <div class="value">
            <div class="flexbox middle space-12">
              <div class="wa-select solid">
                <select
                  v-model="model.type"
                  :class="{ 'state-error': $v.model.type.$error }"
                >
                  <option value="income">{{ $t("income") }}</option>
                  <option value="expense">{{ $t("expense") }}</option>
                </select>
              </div>
              <label
                v-if="model.type === 'expense'"
                @click.prevent="model.is_profit = !model.is_profit"
              >
                <span class="wa-checkbox">
                  <input type="checkbox" :checked="model.is_profit" />
                  <span>
                    <span class="icon">
                      <i class="fas fa-check"></i>
                    </span>
                  </span>
                </span>
                {{ $t("profit") }}
              </label>
            </div>
            <div v-if="model.type === 'income'" class="custom-mt-8">
              <div class="hint">{{ $t("incomeCategoryExplained") }}</div>
            </div>
            <div
              v-if="model.type === 'expense' && !model.is_profit"
              class="custom-mt-8"
            >
              <div class="hint">{{ $t("expenseCategoryExplained") }}</div>
            </div>
            <div v-if="model.is_profit" class="custom-mt-8">
              <div class="hint">{{ $t("profitCategoryExplained") }}</div>
            </div>
          </div>
        </div>

        <div class="field" v-if="model.type">
          <div class="name for-input">
            {{ $t("color") }}
          </div>
          <div class="value">
            <ColorPicker v-model="model.color" />
          </div>
        </div>
      </div>
    </div>

    <div class="dialog-footer">
      <div class="flexbox">
        <div class="flexbox space-12 wide">
          <button @click="submit('category')" class="button purple">
            {{ isModeUpdate ? $t("update") : $t("add") }}
          </button>
          <button @click="close" class="button outlined light-gray">
            <span>{{ $t("cancel") }}</span>
          </button>
        </div>
        <button
          v-if="isModeUpdate"
          @click="remove('category')"
          class="button red"
        >
          {{ $t("delete") }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { required } from 'vuelidate/lib/validators'
import updateEntityMixin from '@/mixins/updateEntityMixin'
import ColorPicker from '@/components/Inputs/ColorPicker'
export default {
  mixins: [updateEntityMixin],

  props: {
    editedItem: {
      type: Object
    },
    type: {
      type: String,
      validator: value => {
        return ['income', 'expense'].indexOf(value) !== -1
      }
    }
  },

  components: {
    ColorPicker
  },

  data () {
    return {
      model: {
        id: null,
        name: '',
        type: '',
        color: '',
        is_profit: false
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

  watch: {
    'model.type' () {
      if (!this.isModeUpdate) {
        this.model.is_profit = false
      }
    }
  },

  created () {
    if (this.type) {
      this.model.type = this.type
      this.model.color = this.model.type === 'income' ? '#00FF00' : '#E57373'
    }
  },

  mounted () {
    this.$refs.focus.focus()
  }
}
</script>
