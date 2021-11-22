<template>
  <div class="dialog-body" style="width: 700px;">
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
          <span class="icon"><i class="fas fa-calendar"></i></span>
        </div>

        <div class="width-25">
          <DropdownWa
            v-model="row.category_id"
            :items="
              $store.state.category.categories.filter(c => c.id !== -1312)
            "
            valuePropName="id"
            :error="$v.data.$each[i].category_id.$error"
            :rowModificator="
              obj =>
                `<span class='icon'><i class='rounded' style='background-color:${obj.color};'></i></span><span>${obj.name}</span>`
            "
            :maxHeight="200"
            class="width-100"
          />
        </div>

        <div class="width-25">
          <DropdownWa
            v-model="row.account_id"
            :items="$store.state.account.accounts"
            valuePropName="id"
            :error="$v.data.$each[i].account_id.$error"
            :rowModificator="
              obj => `${obj.name} (${$helper.currencySignByCode(obj.currency)})`
            "
            :maxHeight="200"
            class="width-100"
          />
        </div>

        <div class="width-25">
          <input-currency
            v-model="row.amount"
            :signed="false"
            :categoryId="row.category_id"
            :accountId="row.account_id"
            :error="$v.data.$each[i].amount.$error"
            placeholder="0"
          />
        </div>

        <div v-if="data.length > 1">
          <a @click.prevent="data.splice(i, 1)" href="#">
            <i class="fas fa-times"></i>
          </a>
        </div>
      </div>
      <div class="align-right">
        <a
          v-if="data.length < 13"
          @click.prevent="data.push({ ...model })"
          href="#"
          class="small"
          >{{ $t("add") }}</a
        >
      </div>
    </div>
    <div class="dialog-footer">
      <div class="flexbox">
        <div class="flexbox space-12 wide">
          <button @click="submit" :disabled="controlsDisabled" class="button">
            {{ $t("add") }}
          </button>
          <button @click="$parent.$emit('close')" class="button light-gray">
            {{ $t("cancel") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { minLength, requiredIf } from 'vuelidate/lib/validators'
import InputCurrency from '@/components/Inputs/InputCurrency'
import DateField from '@/components/Inputs/InputDate'
import DropdownWa from '@/components/Inputs/DropdownWa'
export default {
  components: {
    InputCurrency,
    DateField,
    DropdownWa
  },
  data () {
    return {
      model: {
        amount: null,
        category_id: null,
        account_id: null,
        date: null
      },
      data: new Array(5).fill(null).map(() => ({ ...this.model })),
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

  watch: {
    data: {
      handler (val) {
        this.controlsDisabled = !val.filter(r => r.amount).length
      },
      deep: true
    }
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