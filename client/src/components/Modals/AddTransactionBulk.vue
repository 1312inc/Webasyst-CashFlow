<template>
  <div
    class="dialog-body"
    style="width: 700px;"
  >
    <div class="dialog-header">
      <h2 class="custom-mb-0">
        {{ $t("addMany") }}
      </h2>
    </div>
    <div class="dialog-content">
      <div
        v-for="(row, i) in data"
        :key="i"
        class="flexbox middle custom-mb-12 space-8"
      >
        <div class="state-with-inner-icon left width-25">
          <DateField
            v-model="row.date"
            :class="{
              'state-error': $v.data.$each[i].date.$error
            }"
          />
          <span class="icon"><i class="fas fa-calendar" /></span>
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
          />
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
            @click="$parent.$emit('close')"
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
        date: null
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

    this.data = new Array(5).fill(null).map(() => ({ ...this.model }))
  },

  methods: {
    submit () {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        this.controlsDisabled = true
        const filteredRows = this.data.filter(r => r.amount)

        this.$store
          .dispatch('transactionBulk/bulkCreate', filteredRows)
          .then(() => {
            this.$parent.$emit('close')
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
