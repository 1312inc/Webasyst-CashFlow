<template>
  <div>
    <div class="flexbox middle custom-mb-32">
      <div class="wide flexbox middle">
        <h2 v-if="defaultCategoryType === 'transfer'" class="custom-mb-0">
          {{ $t("newTransfer") }}
        </h2>
        <h2 v-else class="custom-mb-0">
          {{ isModeUpdate ? $t("updateTransaction") : $t("addTransaction") }}
        </h2>
        <span v-if="isModeUpdate && transaction.repeating_id" class="tooltip custom-ml-8 large" :data-title="$t('repeatingTran')">
          <i class="fas fa-redo-alt tw-opacity-50"></i>
        </span>
      </div>
      <div v-if="isModeUpdate" class="large">#{{ transaction.id }}</div>
    </div>

    <div class="fields custom-mb-32">
      <div v-if="isModeUpdate && transaction.repeating_id" class="field">
        <div class="name for-input">
          {{ $t("applyTo.name") }}
        </div>
        <div class="value">
          <div class="custom-mb-8">
            <label>
              <span class="wa-radio">
                <input
                  type="radio"
                  :value="false"
                  v-model="model.apply_to_all_in_future"
                />
                <span></span>
              </span>
              <span class="small custom-ml-4">{{ $t("applyTo.list[0]") }}</span>
            </label>
          </div>
          <label>
            <span class="wa-radio">
              <input
                type="radio"
                :value="true"
                v-model="model.apply_to_all_in_future"
              />
              <span></span>
            </span>
            <span class="small custom-ml-4">{{ $t("applyTo.list[1]") }}</span>
          </label>
        </div>
      </div>

      <div v-if="!isModeUpdate" class="field">
        <div class="name">
          {{ $t("repeat") }}
        </div>
        <div class="value">
          <span
            @click="model.is_repeating = !model.is_repeating"
            class="switch"
            :class="{ 'is-active': model.is_repeating }"
          >
            <span class="switch-toggle"></span>
          </span>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ $t("Amount") }}
        </div>
        <div class="value largest">
          <div class="state-with-inner-icon left">
            <input
              v-model.number="model.amount"
              :class="{ 'state-error': $v.model.amount.$error }"
              type="text"
              class="bold number short"
              placeholder="0"
            />
            <span class="icon">
              <i v-if="transactionType === 'expense'" class="fas fa-minus"></i>
              <i v-if="transactionType === 'income'" class="fas fa-plus"></i>
            </span>
          </div>
          <span v-if="selectedAccount" class="custom-ml-8">{{
            $helper.currencySignByCode(selectedAccount.currency)
          }}</span>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ model.is_repeating ? $t("repeatFrom") : $t("date") }}
        </div>
        <div class="value">
          <div class="state-with-inner-icon left">
            <DateField
              v-model="model.date"
              :class="{ 'state-error': $v.model.date.$error }"
              class="short"
            />
            <span class="icon"><i class="fas fa-calendar"></i></span>
          </div>
        </div>
      </div>

      <div v-if="!isModeUpdate && model.is_repeating" class="field">
        <div class="name for-input">
          {{ $t("howOften.name") }}
        </div>
        <div class="value">
          <div class="wa-select solid">
            <select v-model="model.repeating_interval">
              <option value="month">{{ $t("howOften.list[0]") }}</option>
              <option value="day">{{ $t("howOften.list[1]") }}</option>
              <option value="week">{{ $t("howOften.list[2]") }}</option>
              <option value="year">{{ $t("howOften.list[3]") }}</option>
              <option value="custom">{{ $t("howOften.list[4]") }}</option>
            </select>
          </div>

          <div v-if="model.repeating_interval === 'custom'" class="tw-mt-4">
            {{ $t("howOften.every") }}
            <input
              v-model.number="model.repeating_frequency"
              :class="{ 'state-error': $v.model.repeating_frequency.$error }"
              type="text"
              class="shorter custom-ml-8"
            />
            <div class="wa-select solid custom-ml-8">
              <select v-model="custom_interval">
                <option value="month">
                  {{ $t("howOften.list_short[0]") }}
                </option>
                <option value="day">{{ $t("howOften.list_short[1]") }}</option>
                <option value="week">{{ $t("howOften.list_short[2]") }}</option>
                <option value="year">{{ $t("howOften.list_short[3]") }}</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <div v-if="!isModeUpdate && model.is_repeating" class="field">
        <div class="name for-input">
          {{ $t("endRepeat.name") }}
        </div>
        <div class="value">
          <div class="wa-select solid">
            <select v-model="model.repeating_end_type">
              <option value="never">{{ $t("endRepeat.list[0]") }}</option>
              <option value="after">{{ $t("endRepeat.list[1]") }}</option>
              <option value="ondate">{{ $t("endRepeat.list[2]") }}</option>
            </select>
          </div>

          <div v-if="model.repeating_end_type === 'ondate'" class="tw-mt-4">
            <div class="state-with-inner-icon left">
              <DateField v-model="model.repeating_end_ondate" />
              <span class="icon"><i class="fas fa-calendar"></i></span>
            </div>
          </div>

          <div v-if="model.repeating_end_type === 'after'" class="tw-mt-4">
            <input
              v-model.number="model.repeating_end_after"
              type="text"
              class="shorter"
              :class="{ 'state-error': $v.model.repeating_end_after.$error }"
            />
            <span class="tw-ml-2">{{ $t("endRepeat.occurrences") }}</span>
          </div>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ defaultCategoryType === 'transfer' ? $t("fromAccount") : $t("account") }}
        </div>
        <div class="value">
          <div class="wa-select solid">
            <div v-if="selectedAccount && $helper.isValidHttpUrl(selectedAccount.icon)" class="icon custom-ml-8">
              <img
                :src="selectedAccount.icon"
                alt=""
              />
            </div>
            <select
              v-model="model.account_id"
              :class="{ 'state-error': $v.model.account_id.$error }"
            >
              <option
                :value="account.id"
                v-for="account in accounts"
                :key="account.id"
              >
                {{ account.currency }} – {{ account.name }} ({{
                  $helper.currencySignByCode(account.currency)
                }})
              </option>
            </select>
          </div>
        </div>
      </div>

      <div v-if="defaultCategoryType === 'transfer'" class="field">
        <div class="name for-input">
          {{ $t("toAccount") }}
        </div>
        <div class="value">
          <div class="wa-select solid">
            <select
              v-model="model.transfer_account_id"
            >
              <option
                :value="account.id"
                v-for="account in accounts"
                :key="account.id"
              >
                {{ account.currency }} – {{ account.name }} ({{
                  $helper.currencySignByCode(account.currency)
                }})
              </option>
            </select>
          </div>
        </div>
      </div>

      <div v-if="defaultCategoryType === 'transfer'" class="field">
        <div class="name for-input">
          {{ $t("incomingAmount") }}
        </div>
        <div class="value">
          <div>
            <input
                v-model.number="model.transfer_incoming_amount"
                type="text"
            />
            <span v-if="selectedAccountTransfer" class="custom-ml-8">{{
              $helper.currencySignByCode(selectedAccountTransfer.currency)
            }}</span>
          </div>
          <span v-if="selectedAccount && selectedAccountTransfer && selectedAccount.currency !== selectedAccountTransfer.currency" class="smaller alert warning tw-mt-4 custom-mb-0">
            <i class="fas fa-exclamation-triangle"></i>
            {{ selectedAccount.currency }} → {{ selectedAccountTransfer.currency }}.
            {{ $t("transferMessage") }}
          </span>
        </div>
      </div>

      <div v-if="defaultCategoryType !== 'transfer'" class="field">
        <div class="name for-input">
          {{ $t("category") }}
        </div>
        <div class="value">
          <div class="wa-select solid">
            <span v-if="selectedCategory" class="icon custom-ml-8"
              ><i
                class="rounded"
                :style="`background-color:${selectedCategory.color};`"
              ></i
            ></span>
            <select
              v-model="model.category_id"
              :class="{ 'state-error': $v.model.category_id.$error }"
            >
              <option
                :value="category.id"
                v-for="category in categoriesInSelect"
                :key="category.id"
              >
                {{ category.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div v-if="defaultCategoryType !== 'transfer'" class="field">
        <div class="name for-input">
          {{ $t("contractor") }}
        </div>
        <div class="value">
          <div class="state-with-inner-icon left">
            <input v-model="model.contractor_contact_id" type="text" />
            <span class="icon"><i class="fas fa-user"></i></span>
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
            class="wide bold"
            rows="4"
            style="resize: none; height: auto"
            :placeholder="$t('optional')"
          ></textarea>
        </div>
      </div>
    </div>

    <div class="flexbox">
      <div class="flexbox space-12 wide">
        <button @click="submit" class="button purple">
          {{ isModeUpdate ? $t("update") : $t("add") }}
        </button>
        <button @click="close" class="button light-gray">
          {{ $t("cancel") }}
        </button>
      </div>
      <button v-if="isModeUpdate" @click="remove" class="button red outlined">
        <span>{{ $t("delete") }}</span>
      </button>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import { required, requiredIf, integer, numeric } from 'vuelidate/lib/validators'
import DateField from '@/components/DateField'
export default {
  props: {
    transaction: {
      type: Object
    },

    defaultCategoryType: {
      type: String
    }
  },

  components: {
    DateField
  },

  data () {
    return {
      model: {
        id: null,
        amount: null,
        date: '',
        account_id: null,
        category_id: null,
        contractor_contact_id: null,
        contractor: null,
        description: '',
        is_repeating: false,
        repeating_frequency: 1,
        repeating_interval: 'month',
        repeating_end_type: 'never',
        repeating_end_after: null,
        repeating_end_ondate: '',
        transfer_account_id: null,
        transfer_incoming_amount: null,
        apply_to_all_in_future: false
      },
      custom_interval: 'month'
    }
  },

  validations: {
    model: {
      amount: {
        required,
        numeric
      },
      date: {
        required
      },
      account_id: {
        required
      },
      category_id: {
        required
      },
      repeating_frequency: {
        required: requiredIf(function () {
          return this.model.repeating_interval === 'custom'
        }),
        integer
      },
      repeating_end_after: {
        required: requiredIf(function () {
          return this.model.repeating_end_type === 'after'
        }),
        integer
      }
    }
  },

  computed: {
    ...mapState('account', ['accounts']),
    ...mapState('category', ['categories']),
    ...mapGetters({
      getAccountById: 'account/getById',
      getCategoryById: 'category/getById',
      getCategoryByType: 'category/getByType'
    }),

    isModeUpdate () {
      return !!this.transaction
    },

    selectedAccount () {
      return this.getAccountById(this.model.account_id)
    },

    selectedAccountTransfer () {
      return this.getAccountById(this.model.transfer_account_id)
    },

    selectedCategory () {
      return this.getCategoryById(this.model.category_id)
    },

    transactionType () {
      return this.selectedCategory?.type || this.defaultCategoryType
    },

    categoriesInSelect () {
      return this.getCategoryByType(this.transactionType)
    }

  },

  watch: {
    'model.repeating_interval' (val) {
      if (val !== 'custom') this.model.repeating_frequency = 1
    }
  },

  created () {
    if (this.isModeUpdate) {
      // for (const prop in this.model) {
      //   this.model[prop] = this.transaction[prop] || this.model[prop]
      // }
      this.model = { ...this.model, ...this.transaction }
      this.model.amount = Math.abs(this.model.amount)
    }
    if (this.defaultCategoryType === 'transfer') {
      this.model.category_id = -1312
    }
  },

  methods: {
    submit () {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        const model = { ...this.model }
        if (model.repeating_interval === 'custom') {
          model.repeating_interval = this.custom_interval
        }

        this.$store
          .dispatch('transaction/update', model)
          .then(() => {
            this.$noty.success('Транзакция успешно обновлена')
            this.close()
          })
          .catch(() => {
            this.$noty.error('Oops, something went wrong!')
          })
      }
    },

    remove () {
      if (confirm(this.$t('transactionDeleteWarning'))) {
        this.$store
          .dispatch('transaction/delete', this.model.id)
          .then(() => {
            this.$noty.success('Транзакция успешно удалена')
            this.close()
          })
          .catch(() => {
            this.$noty.error('Oops, something went wrong!')
          })
      }
    },

    close () {
      this.$parent.$emit('close')
    }
  }
}
</script>
