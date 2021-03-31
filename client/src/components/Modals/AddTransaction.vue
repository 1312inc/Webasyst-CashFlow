<template>
  <div class="dialog-body">
    <div class="dialog-header">
      <div class="flexbox middle">
        <div class="wide flexbox middle">
          <h2 v-if="defaultCategoryType === 'transfer'" class="custom-mb-0">
            {{ $t("newTransfer") }}
          </h2>
          <h2 v-else class="custom-mb-0">
            {{ isModeUpdate ? $t("updateTransaction") : $t("addTransaction") }}
          </h2>
          <span
            v-if="isModeUpdate && transaction.repeating_id"
            class="tooltip custom-ml-8 large"
            :data-title="$t('repeatingTran')"
          >
            <i class="fas fa-redo-alt opacity-50"></i>
          </span>
        </div>
        <div v-if="isModeUpdate" class="large">#{{ transaction.id }}</div>
      </div>
    </div>

    <div class="dialog-content">
      <div class="fields">
        <div class="field">
          <div class="name for-input custom-pt-12">
            {{ $t("amount") }}
          </div>
          <div class="value large bold">
            <div class="state-with-inner-icon left">
              <input-currency
                v-model="model.amount"
                @keyup.enter="submit"
                :signed="false"
                :class="{ 'state-error': $v.model.amount.$error }"
                ref="focus"
                type="text"
                class="bold number short"
                placeholder="0"
              />
              <span
                class="icon"
                style="opacity: 1;"
                :class="{
                  'text-orange': transactionType === 'expense',
                  'text-green': transactionType === 'income',
                }"
              >
                <i
                  v-if="transactionType === 'expense'"
                  class="fas fa-minus"
                ></i>
                <i v-if="transactionType === 'income'" class="fas fa-plus"></i>
              </span>
            </div>
            <span v-if="selectedAccount" class="custom-ml-4">{{
              $helper.currencySignByCode(selectedAccount.currency)
            }}</span>
          </div>
        </div>

        <div class="field">
          <div class="name for-input">
            {{
              defaultCategoryType === "transfer"
                ? $t("fromAccount")
                : $t("account")
            }}
          </div>
          <div class="value">
            <div class="wa-select solid">
              <div
                v-if="
                  selectedAccount &&
                  $helper.isValidHttpUrl(selectedAccount.icon)
                "
                class="icon size-20 custom-ml-8"
                style="margin-right: -0.25rem;"
              >
                <img :src="selectedAccount.icon" alt="" />
              </div>
              <select
                v-model="model.account_id"
                :class="{ 'state-error': $v.model.account_id.$error }"
                style="min-width: 0"
              >
                <option
                  :value="account.id"
                  v-for="account in accounts"
                  :key="account.id"
                >
                  {{ account.name }} ({{
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
                :class="{ 'state-error': $v.model.transfer_account_id.$error }"
                style="min-width: 0"
              >
                <option
                  :value="account.id"
                  v-for="account in accountsTransfer"
                  :key="account.id"
                >
                  {{ account.name }} ({{
                    $helper.currencySignByCode(account.currency)
                  }})
                </option>
              </select>
            </div>
          </div>
        </div>

        <TransitionCollapseHeight>
          <div v-if="showTransferIncomingAmount">
            <div class="field custom-pt-16">
              <div class="name for-input">
                {{ $t("incomingAmount") }}
              </div>
              <div class="value">
                <div class="bold">
                  <input-currency
                    v-model="model.transfer_incoming_amount"
                    :signed="false"
                    :class="{
                      'state-error': $v.model.transfer_incoming_amount.$error,
                    }"
                    type="text"
                    class="bold number short"
                    placeholder="0"
                  />
                  <span v-if="selectedAccountTransfer" class="custom-ml-4">{{
                    $helper.currencySignByCode(selectedAccountTransfer.currency)
                  }}</span>
                </div>
                <div class="state-caution-hint custom-mt-8 custom-mb-0">
                  <i class="fas fa-exclamation-triangle"></i>
                  <strong>
                    {{ selectedAccount.currency }} →
                    {{ selectedAccountTransfer.currency }}.
                  </strong>
                  {{ $t("transferMessage") }}
                </div>
              </div>
            </div>
          </div>
        </TransitionCollapseHeight>

        <div v-if="defaultCategoryType !== 'transfer'" class="field">
          <div class="name for-input">
            {{ $t("category") }}
          </div>
          <div class="value">
            <div class="wa-select solid">
              <span v-if="selectedCategory" class="icon custom-ml-8"
                style="margin-right: -0.25rem;"
                ><i
                  class="rounded"
                  :style="`background-color:${selectedCategory.color};`"
                ></i
              ></span>
              <select
                v-model="model.category_id"
                :class="{ 'state-error': $v.model.category_id.$error }"
                style="min-width: 0"
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
            <InputContractor
              :defaultContractor="model.contractor_contact"
              @newContractor="
                (name) => {
                  model.contractor = name;
                  model.contractor_contact_id = null;
                }
              "
              @changeContractor="
                (id) => {
                  model.contractor = null;
                  model.contractor_contact_id = id;
                }
              "
            />
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
              rows="3"
              style="resize: none; height: auto"
            ></textarea>
          </div>
        </div>

        <div v-if="isModeUpdate && transaction.repeating_id" class="field">
          <div class="name for-checkbox">
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
                <span class="small custom-ml-4">{{
                  $t("applyTo.list[0]")
                }}</span>
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
          <div class="name for-input">
            {{ $t("repeat") }}
          </div>
          <div class="value">
            <div class="toggle">
              <span
                @click="model.is_repeating = false"
                :class="{ selected: !model.is_repeating }"
                >{{ $t("oneTime") }}</span
              >
              <span
                @click="model.is_repeating = true"
                :class="{ selected: model.is_repeating }"
                >{{ $t("repeating") }}</span
              >
            </div>
          </div>
        </div>

        <div class="field">
          <div class="name for-input">
            {{ model.is_repeating ? $t("repeatFrom") : $t("date") }}
          </div>
          <div class="value">
            <div class="flexbox space-12 middle">
              <div class="state-with-inner-icon left">
                <DateField
                  v-model="model.date"
                  :class="{ 'state-error': $v.model.date.$error }"
                  class="short"
                />
                <span class="icon"><i class="fas fa-calendar"></i></span>
              </div>
              <label @click.prevent="model.is_onbadge = !model.is_onbadge">
                <span class="wa-checkbox">
                  <input type="checkbox" :checked="model.is_onbadge" />
                  <span>
                    <span class="icon">
                      <i class="fas fa-check"></i>
                    </span>
                  </span>
                </span>
                {{ $t("notifyMe") }}
              </label>
            </div>
            <div v-if="model.is_onbadge" class="custom-mt-8">
              <div class="hint">
                {{ $t("notifyMeAlert") }}
                <span class="badge smaller">1</span>
              </div>
            </div>
          </div>
        </div>

        <TransitionCollapseHeight>
          <div v-if="!isModeUpdate && model.is_repeating">
            <div class="field custom-pt-16">
              <div class="name for-input">
                {{ $t("howOften.name") }}
              </div>
              <div class="value">
                <div class="wa-select small solid">
                  <select v-model="model.repeating_interval">
                    <option value="month">{{ $t("howOften.list[0]") }}</option>
                    <option value="day">{{ $t("howOften.list[1]") }}</option>
                    <option value="week">{{ $t("howOften.list[2]") }}</option>
                    <option value="year">{{ $t("howOften.list[3]") }}</option>
                    <option value="custom">{{ $t("howOften.list[4]") }}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </TransitionCollapseHeight>

        <TransitionCollapseHeight>
          <div
            v-if="model.is_repeating && model.repeating_interval === 'custom'"
          >
            <div class="field custom-pt-16">
              <div class="name for-input">

              </div>
              <div class="value">
                <div>
                  <span class="small">{{ $t("howOften.every") }}</span>
                  <input
                    v-model.number="model.repeating_frequency"
                    :class="{
                      'state-error': $v.model.repeating_frequency.$error,
                    }"
                    type="text"
                    class="shortest small custom-ml-4 number"
                  />
                  <div class="wa-select small solid custom-ml-8">
                    <select v-model="custom_interval">
                      <option value="month">
                        {{ $t("howOften.list_short[0]") }}
                      </option>
                      <option value="day">
                        {{ $t("howOften.list_short[1]") }}
                      </option>
                      <option value="week">
                        {{ $t("howOften.list_short[2]") }}
                      </option>
                      <option value="year">
                        {{ $t("howOften.list_short[3]") }}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </TransitionCollapseHeight>

        <TransitionCollapseHeight>
          <div v-if="!isModeUpdate && model.is_repeating">
            <div class="field custom-pt-16">
              <div class="name for-input">
                {{ $t("endRepeat.name") }}
              </div>
              <div class="value">
                <div class="wa-select small solid">
                  <select v-model="model.repeating_end_type">
                    <option value="never">{{ $t("endRepeat.list[0]") }}</option>
                    <option value="after">{{ $t("endRepeat.list[1]") }}</option>
                    <option value="ondate">
                      {{ $t("endRepeat.list[2]") }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </TransitionCollapseHeight>

        <TransitionCollapseHeight>
          <div
            v-if="
              !isModeUpdate &&
              model.is_repeating &&
              model.repeating_end_type !== 'never'
            "
          >
            <div class="field custom-pt-16">
              <div class="name for-input"></div>
              <div class="value">
                <div
                  v-if="model.repeating_end_type === 'ondate'"
                  class="state-with-inner-icon left small"
                >
                  <DateField v-model="model.repeating_end_ondate" />
                  <span class="icon"><i class="fas fa-calendar"></i></span>
                </div>

                <div v-if="model.repeating_end_type === 'after'">
                  <input
                    v-model.number="model.repeating_end_after"
                    type="text"
                    class="shortest small number"
                    :class="{
                      'state-error': $v.model.repeating_end_after.$error,
                    }"
                  />
                  <span class="small custom-ml-8">{{
                    $t("endRepeat.occurrences")
                  }}</span>
                </div>
              </div>
            </div>
          </div>
        </TransitionCollapseHeight>
      </div>
    </div>

    <div class="dialog-footer">
      <div class="flexbox">
        <div class="flexbox space-12 wide">
          <button
            @click="submit"
            :class="{
              orange: transactionType === 'expense',
              green: transactionType === 'income',
            }"
            class="button"
          >
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
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import { required, requiredIf, integer } from 'vuelidate/lib/validators'
import InputCurrency from '@/components/Inputs/InputCurrency'
import InputContractor from '@/components/Inputs/InputContractor'
import DateField from '@/components/Inputs/InputDate'
import TransitionCollapseHeight from '@/components/Transitions/TransitionCollapseHeight'
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
    InputCurrency,
    InputContractor,
    DateField,
    TransitionCollapseHeight
  },

  data () {
    return {
      model: {
        id: null,
        amount: null,
        date: '',
        account_id: null,
        category_id: null,
        contractor: null,
        contractor_contact: null,
        contractor_contact_id: null,
        description: '',
        is_onbadge: false,
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
        required
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
      },
      transfer_account_id: {
        required: requiredIf(function () {
          return this.defaultCategoryType === 'transfer'
        })
      },
      transfer_incoming_amount: {
        required: requiredIf(function () {
          return this.showTransferIncomingAmount
        })
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

    accountsTransfer () {
      return this.accounts.filter(a => a.id !== this.model.account_id)
    },

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
    },

    showTransferIncomingAmount () {
      return (
        this.defaultCategoryType === 'transfer' &&
        this.selectedAccount &&
        this.selectedAccountTransfer &&
        this.selectedAccount.currency !== this.selectedAccountTransfer.currency
      )
    }
  },

  watch: {
    'model.repeating_interval' (val) {
      if (val !== 'custom') this.model.repeating_frequency = 1
    },
    'model.account_id' () {
      this.model.transfer_account_id = null
    }
  },

  created () {
    // Set default category/account
    if (!this.isModeUpdate) {
      const currentType = this.$store.state.currentType
      if (currentType === 'account' || currentType === 'category') {
        this.model[`${currentType}_id`] = this.$store.state.currentTypeId
      }
    }

    // if only one account make it active in the select
    if (!this.isModeUpdate && this.accounts.length === 1) {
      this.model.account_id = this.accounts[0].id
    }

    // Fill data if Update mode
    if (this.isModeUpdate) {
      for (const prop in this.model) {
        this.model[prop] = this.transaction[prop] || this.model[prop]
      }
      this.model.amount = `${Math.abs(this.model.amount)}`
    }

    if (this.defaultCategoryType === 'transfer') {
      this.model.category_id = -1312
    }
  },

  mounted () {
    this.$refs.focus.$el.focus()
  },

  methods: {
    submit () {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        const model = { ...this.model }
        if (model.repeating_interval === 'custom') {
          model.repeating_interval = this.custom_interval
        }
        if (!this.showTransferIncomingAmount) {
          model.transfer_incoming_amount = this.model.amount
        }

        this.$store.dispatch('transaction/update', model).then(() => {
          this.close()
        })
      }
    },

    remove () {
      if (confirm(this.$t('transactionDeleteWarning'))) {
        this.$store.dispatch('transaction/delete', {
          id: this.model.id,
          all_repeating: this.model.apply_to_all_in_future
        }).then(() => {
          this.close()
        })
      }
    },

    close () {
      this.$parent.$emit('close')
    }
  }
}
</script>
