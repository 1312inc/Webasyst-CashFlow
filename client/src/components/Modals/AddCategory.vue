<template>
  <div
    class="dialog-body"
    style="overflow: initial;"
  >
    <div class="dialog-header">
      <div class="flexbox middle full-width custom-mb-12">
        <h2 class="custom-m-0">
          {{ isModeUpdate ? $t("updateCategory") : $t("addCategory") }}
        </h2>
        <button
          v-if="isModeUpdate"
          :disabled="controlsDisabled"
          class="button outlined red mobile-only"
          @click="remove('category')"
        >
          <span><i class="fas fa-trash-alt" /></span>
        </button>
      </div>
    </div>
    <div class="dialog-content">
      <div class="fields">
        <div class="field">
          <div class="name for-input">
            {{ $t("name") }}
          </div>
          <div class="value">
            <input
              ref="focus"
              v-model="model.name"
              :class="{ 'state-error': $v.model.name.$error }"
              class="bold large"
              type="text"
              @keyup.enter="submit('category')"
            >
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
                  <option value="income">
                    {{ $t("income") }}
                  </option>
                  <option value="expense">
                    {{ $t("expense") }}
                  </option>
                </select>
              </div>
              <label
                v-if="model.type === 'expense'"
                @click.prevent="model.is_profit = !model.is_profit"
              >
                <span class="wa-checkbox">
                  <input
                    type="checkbox"
                    :checked="model.is_profit"
                  >
                  <span>
                    <span class="icon">
                      <i class="fas fa-check" />
                    </span>
                  </span>
                </span>
                {{ $t("profit") }}
                <span class="text-blue">
                  <i class="fas fa-coins" />
                </span>
              </label>
            </div>
            <div
              v-if="model.type === 'income'"
              class="custom-mt-8"
            >
              <div class="hint">
                {{ $t("incomeCategoryExplained") }}
              </div>
            </div>
            <div
              v-if="model.type === 'expense' && !model.is_profit"
              class="custom-mt-8"
            >
              <div class="hint">
                {{ $t("expenseCategoryExplained") }}
              </div>
            </div>
            <div
              v-if="model.is_profit"
              class="custom-mt-8"
            >
              <div class="hint">
                {{ $t("profitCategoryExplained") }}
              </div>
            </div>
          </div>
        </div>

        <div
          v-if="showSelectParent"
          class="field"
        >
          <div class="name for-input">
            {{ $t("parentCategory") }}
          </div>
          <div class="value">
            <DropdownWa
              v-model="model.parent_category_id"
              :items="parentsList"
              value-prop-name="id"
              :row-modificator="$_rowModificatorMixin_rowModificator_category"
              :max-height="200"
              class="width-100"
            />
          </div>
        </div>

        <div
          v-if="!model.parent_category_id"
          class="field"
        >
          <div class="name for-input">
            {{ $t("color") }}
          </div>
          <div class="value">
            <ColorPicker v-model="model.color" />
          </div>
        </div>

        <div class="field">
          <div class="name for-input">
            {{ $t("icon") }}
          </div>
          <div class="value">
            <FontAwsomeSelector
              v-model="model.glyph"
              :color="model.color"
            />
          </div>
        </div>
      </div>
    </div>

    <div class="dialog-footer">
      <div class="flexbox">
        <div class="flexbox space-12 wide">
          <button
            :disabled="controlsDisabled"
            class="button purple"
            @click="submit('category')"
          >
            {{ isModeUpdate ? $t("update") : $t("add") }}
          </button>
          <button
            class="button light-gray"
            @click="close"
          >
            {{ $t("cancel") }}
          </button>
        </div>
        <button
          v-if="isModeUpdate"
          :disabled="controlsDisabled"
          class="button outlined red desktop-and-tablet-only"
          @click="remove('category')"
        >
          <span>{{ $t("delete") }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { required } from 'vuelidate/dist/validators.min.js'
import updateEntityMixin from '@/mixins/updateEntityMixin'
import rowModificatorMixin from '@/mixins/rowModificatorMixin.js'
import DropdownWa from '@/components/Inputs/DropdownWa'
import ColorPicker from '@/components/Inputs/ColorPicker'
import FontAwsomeSelector from '../Inputs/FontAwsomeSelector.vue'
export default {

  components: {
    ColorPicker,
    DropdownWa,
    FontAwsomeSelector
  },
  mixins: [updateEntityMixin, rowModificatorMixin],

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

  data () {
    return {
      model: {
        id: null,
        name: '',
        type: '',
        color: '',
        is_profit: false,
        parent_category_id: null,
        glyph: null
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
    parentsList () {
      return this.$store.getters['category/sortedCategories'].filter(
        c =>
          !c.parent_category_id &&
          this.editedItem?.id !== c.id &&
          c.type === this.model.type &&
          ![-1, -2].includes(c.id)
      )
    },

    showSelectParent () {
      return this.editedItem
        ? this.$store.getters['category/getChildren'](this.editedItem.id)
          .length < 1
        : true
    }
  },

  watch: {
    'model.parent_category_id' (val) {
      this.model.color = val
        ? this.$store.getters['category/getById'](val).color
        : this.model.type === 'income'
          ? '#11CC22'
          : '#EF4B35'
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
      this.model.color = this.model.type === 'income' ? '#11CC22' : '#EF4B35'
    }
  },

  mounted () {
    this.$refs.focus.focus()
  }
}
</script>
