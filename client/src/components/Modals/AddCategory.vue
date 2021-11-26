<template>
  <div class="dialog-body" style="overflow: initial;">
    <div class="dialog-header">
      <h2>
        {{ isModeUpdate ? $t("updateCategory") : $t("addCategory") }}
      </h2>
    </div>
    <div class="dialog-content" style="overflow: initial;">
      <div class="fields">
        <div class="field">
          <div class="name for-input">
            {{ $t("name") }}
          </div>
          <div class="value">
            <input
              v-model="model.name"
              @keyup.enter="submit('category')"
              :class="{ 'state-error': $v.model.name.$error }"
              ref="focus"
              class="bold large"
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
                <span class="text-blue">
                  <i class="fas fa-coins"></i>
                </span>
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

        <div class="field">
          <div class="name for-input">
            {{ $t("parentCategory") }}
          </div>
          <div class="value">
            <DropdownWa
              v-model="model.parent_category_id"
              :defaultValue="$t('noCategory')"
              :items="categories"
              valuePropName="id"
              :rowModificator="
                obj =>
                  `<span class='icon'><i class='rounded' style='background-color:${obj.color};'></i></span><span>${obj.name}</span>`
              "
              :maxHeight="200"
              class="width-100"
            />
            <div v-if="model.parent_category_id" class="hint custom-mt-8">
              This category will be shown in the app under the selected parent
              category
            </div>
          </div>
        </div>

        <div class="field" v-if="!model.parent_category_id">
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
          <button
            @click="submit('category')"
            :disabled="controlsDisabled"
            class="button purple"
          >
            {{ isModeUpdate ? $t("update") : $t("add") }}
          </button>
          <button @click="close" class="button light-gray">
            {{ $t("cancel") }}
          </button>
        </div>
        <button
          v-if="isModeUpdate"
          @click="remove('category')"
          :disabled="controlsDisabled"
          class="button outlined red"
        >
          <span>{{ $t("delete") }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { required } from 'vuelidate/lib/validators'
import updateEntityMixin from '@/mixins/updateEntityMixin'
import DropdownWa from '@/components/Inputs/DropdownWa'
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
    ColorPicker,
    DropdownWa
  },

  data () {
    return {
      model: {
        id: null,
        name: '',
        type: '',
        color: '',
        is_profit: false,
        parent_category_id: null
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
    categories () {
      return this.$store.state.category.categories.filter(
        c => c.parent_category_id === null && c.type === this.model.type
      )
    }
  },

  watch: {
    'model.parent_category_id' (val) {
      this.model.color = val
        ? this.$store.getters['category/getById'](val).color
        : this.model.type === 'income'
          ? '#00FF00'
          : '#E57373'
    },
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
