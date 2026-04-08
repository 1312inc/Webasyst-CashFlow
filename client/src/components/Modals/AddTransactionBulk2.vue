<template>
  <div>
    <div class="dialog-content">
      <div
        v-for="(row, i) in data"
        :key="i"
        class="flexbox middle custom-mb-12 space-8"
      >
        <div
          v-show="row.repeating_interval === null"
          class="state-with-inner-icon left width-25"
        >
          <DateField
            v-model="row.date"
            :class="{
              'state-error': $v.data.$each[i].date.$error
            }"
          />
          <span class="icon"><i class="fas fa-calendar" /></span>
        </div>

        <div
          v-if="row.repeating_interval"
          class="wa-select small solid"
          style="min-width: 164px;"
        >
          <select
            v-model="row.repeating_interval"
            @change="onIntervalChange(row)"
          >
            <option value="month">
              {{ $t("howOften.list[0]") }}
            </option>
            <option value="day">
              {{ $t("howOften.list[1]") }}
            </option>
            <option value="week">
              {{ $t("howOften.list[2]") }}
            </option>
            <option value="year">
              {{ $t("howOften.list[3]") }}
            </option>
            <option :value="null">
              {{ $t("howOften.list[5]") }}
            </option>
          </select>
        </div>

        <div class="width-25">
          <DropdownWa
            v-model="row.category_id"
            :items="[$store.getters['category/getByType']('income'), $store.getters['category/getByType']('expense')]"
            :groups-labels="[$t('income'), $t('expense')]"
            value-prop-name="id"
            :error="$v.data.$each[i].category_id.$error"
            :row-modificator="$_rowModificatorMixin_rowModificator_category"
            :max-height="200"
            class="width-100"
            @input="onCategoryChange(row, $event)"
          />
        </div>

        <div class="text-gray">
          <div v-show="row.type === 'income'">
            <i class="fa fa-arrow-right" />
          </div>
          <div v-show="row.type === 'expense'">
            <i class="fa fa-arrow-left" />
          </div>
        </div>

        <div class="width-25">
          <DropdownWa
            v-model="row.account_id"
            :items="$store.state.account.accounts"
            value-prop-name="id"
            :error="$v.data.$each[i].account_id.$error"
            :row-modificator="$_rowModificatorMixin_rowModificator_account"
            :max-height="200"
            class="width-100"
          />
        </div>

        <div class="width-25">
          <input-currency
            v-model="row.amount"
            :signed="false"
            :category-id="row.category_id"
            :account-id="row.account_id"
            :error="$v.data.$each[i].amount.$error"
            placeholder="0"
          />
        </div>

        <div v-if="data.length > 1">
          <a @click.prevent="data.splice(i, 1)">
            <i class="fas fa-times-circle text-light-gray" />
          </a>
        </div>
      </div>
      <div class="align-right">
        <a

          class="small"
          @click.prevent="data.push({ ...model })"
        >{{ $t("addmore") }}</a>
      </div>
    </div>
    <div class="dialog-footer">
      <div class="flexbox">
        <div class="flexbox space-12 wide">
          <button
            :disabled="controlsDisabled"
            class="button"
            @click="submit"
          >
            {{ $t("add") }}
          </button>
          <button
            class="button light-gray"
            @click="$emit('close')"
          >
            {{ $t("cancel") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { minLength, requiredIf } from 'vuelidate/dist/validators.min.js'
import InputCurrency from '@/components/Inputs/InputCurrency'
import DateField from '@/components/Inputs/InputDate'
import DropdownWa from '@/components/Inputs/DropdownWa'
import rowModificatorMixin from '@/mixins/rowModificatorMixin.js'
import entityPageMixin from '@/mixins/entityPageMixin'

export default {
  components: {
    InputCurrency,
    DateField,
    DropdownWa
  },

  mixins: [rowModificatorMixin, entityPageMixin],

  data () {
    return {
      model: {
        amount: null,
        category_id: null,
        account_id: null,
        date: null,
        type: '',
        is_repeating: true,
        repeating_interval: 'month'
      },
      data: [],
      controlsDisabled: true
    }
  },

  validations: {
    data: {
      $each: {
        amount: {
          minLength: minLength(1)
        },
        category_id: {
          required: requiredIf(row => row.amount)
        },
        account_id: {
          required: requiredIf(row => row.amount)
        },
        date: {
          required: requiredIf(row => row.amount)
        }
      }
    }
  },

  computed: {
    accounts () {
      return this.$store.state.account.accounts
    }
  },

  watch: {
    data: {
      handler (val) {
        this.controlsDisabled = !val.filter(r => r.amount).length
      },
      deep: true
    }
  },

  created () {
    const currentType = this.$store.state.currentType
    if (currentType === 'account' || currentType === 'category') {
      this.model[`${currentType}_id`] = this.$store.state.currentTypeId
    }

    if (this.accounts.length === 1) {
      this.model.account_id = this.accounts[0].id
    }

    this.data = Array(5).fill(null).map(() => ({ ...this.model }))
  },

  methods: {
    getCategoryType (id) {
      const category = this.$store.getters['category/getById'](id)
      return category ? category.type : ''
    },

    onCategoryChange (row, categoryId) {
      row.type = this.getCategoryType(categoryId)
    },

    onIntervalChange (row) {
      row.is_repeating = !!row.repeating_interval
    },

    submit () {
      this.$v.$touch()

      if (!this.$v.$invalid) {
        this.controlsDisabled = true
        const filteredRows = this.data.filter(r => r.amount)

        this.$store
          .dispatch('transactionBulk/bulkCreate', filteredRows)
          .then(() => {
            this.$emit('close')
          })
          .finally(() => {
            this.controlsDisabled = false
          })
      }
    }
  }
}
</script>

<style scoped>
.flexbox > div {
  min-width: 0;
}
</style>
