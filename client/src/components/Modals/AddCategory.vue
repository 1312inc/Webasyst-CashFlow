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

        <div v-if="showSelectParent" class="field">
          <div class="name for-input">
            {{ $t("parentCategory") }}
          </div>
          <div class="value">
            <DropdownWa
              v-model="model.parent_category_id"
              :items="parentsList"
              valuePropName="id"
              :rowModificator="$_rowModificatorMixin_rowModificator_category"
              :maxHeight="200"
              class="width-100"
            />
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

        <div class="field">
          <div class="name for-input">
            {{ $t("icon") }}
          </div>
          <div class="value">
            <FontAwsomeSelector v-model="model.glyph" :color="model.color" />
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
import { required } from 'vuelidate/dist/validators.min.js'
import updateEntityMixin from '@/mixins/updateEntityMixin'
import rowModificatorMixin from '@/mixins/rowModificatorMixin.js'
import DropdownWa from '@/components/Inputs/DropdownWa'
import ColorPicker from '@/components/Inputs/ColorPicker'
import FontAwsomeSelector from '../Inputs/FontAwsomeSelector.vue'
export default {
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

  components: {
    ColorPicker,
    DropdownWa,
    FontAwsomeSelector
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
          c.parent_category_id === null &&
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
