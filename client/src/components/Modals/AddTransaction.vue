<template>
  <div class="dialog-body" style="width: 750px">
    <div class="dialog-header">
      <div class="flexbox middle">
        <div class="wide flexbox middle">
          <h2 v-if="transactionType === 'transfer'" class="custom-mb-0">
            {{ $t("newTransfer") }}
          </h2>
          <h2 v-else class="custom-mb-0">
            {{ isModeUpdate ? $t("updateTransaction") : $t("addTransaction") }}
          </h2>
          <span
            v-if="isModeUpdate && transaction.repeating_id"
            class="custom-ml-8 larger"
            :title="$t('repeatingTran')"
          >
            <i class="fas fa-redo opacity-30"></i>
          </span>
        </div>
        <div v-if="isModeUpdate && transaction.create_contact_id">
          <span class="icon userpic size-32" v-wa-tippy="tippyContent">
            <img :src="transaction.create_contact.userpic" alt="" />
          </span>
        </div>
      </div>
    </div>

    <div class="dialog-content">
      <div class="flexbox space-24 wrap-mobile">
        <div style="flex: 0;">
          <div class="custom-mb-16 c-inline-calendar">
            <DateField
              v-model="model.date"
              :inline="true"
              :class="{ 'state-error': $v.model.date.$error }"
              :disabled="model.apply_to_all_in_future"
            />
          </div>

          <div class="custom-mb-16">
            {{
              $t("transactionDate", { date: $moment(model.date).format("LL") })
            }}
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

          <div v-if="model.is_onbadge" class="custom-mt-8">
            <div class="hint">
              {{ $t("notifyMeAlert") }}&nbsp;<span class="badge smaller"
                >1</span
              >
            </div>
          </div>
        </div>
        <div class="wide custom-mt-24-mobile">
          <!-- Start Toggle type section -->
          <div v-if="!isModeUpdate" class="toggle custom-mb-16">
            <span
              v-for="(type, i) in ['income', 'expense', ...(accounts.length > 1 ? ['transfer'] : [])]"
              :key="i"
              @click="transactionType = type"
              :class="{ selected: transactionType === type }"
              >{{ $t(type) }}</span
            >
          </div>
          <!-- End Toggle type section -->

          <!-- Start Currency Input section -->
          <div class="custom-mb-16">
            <input-currency
              v-model="model.amount"
              @keyEnter="submit"
              :signed="false"
              :categoryId="model.category_id"
              :accountId="model.account_id"
              :error="$v.model.amount.$error"
              :transactionType="transactionType"
              :focused="true"
              class="larger"
              placeholder="0"
            />
          </div>
          <!-- End Currency Input section -->

          <!-- Start Categories section -->
          <div class="flexbox middle custom-mb-16">
            <div v-if="transactionType !== 'transfer'" class="width-50">
              <DropdownWa
                v-model="model.category_id"
                :error="$v.model.category_id.$error"
                :label="$t('category')"
                :items="getCategoryByType(transactionType)"
                valuePropName="id"
                :rowModificator="$_rowModificatorMixin_rowModificator_category"
                :maxHeight="200"
                class="width-100"
              />
            </div>
            <div
              v-if="transactionType !== 'transfer'"
              class="custom-px-8 gray"
              :style="
                transactionType === 'expense'
                  ? 'transform: rotate(180deg);'
                  : ''
              "
            >
              <i class="fas fa-arrow-right"></i>
            </div>
            <div
              :class="
                transactionType === 'transfer'
                  ? 'flexbox middle width-100'
                  : 'width-50'
              "
            >
              <div :class="{ 'width-50': transactionType === 'transfer' }">
                <DropdownWa
                  v-model="model.account_id"
                  :error="$v.model.account_id.$error"
                  :label="
                    transactionType === 'transfer' ||
                    transactionType === 'expense'
                      ? $t('fromAccount')
                      : $t('toAccount')
                  "
                  :items="accounts"
                  valuePropName="id"
                  :rowModificator="$_rowModificatorMixin_rowModificator_account"
                  :isRight="transactionType !== 'transfer'"
                  :maxHeight="200"
                  class="width-100"
                />
              </div>
              <div
                v-if="transactionType === 'transfer'"
                class="custom-px-4 gray"
              >
                <i class="fas fa-arrow-right"></i>
              </div>
              <div
                :class="{ 'width-50': transactionType === 'transfer' }"
                v-if="transactionType === 'transfer'"
              >
                <DropdownWa
                  v-model="model.transfer_account_id"
                  :error="$v.model.transfer_account_id.$error"
                  :label="$t('toAccount')"
                  :items="accountsTransfer"
                  valuePropName="id"
                  :rowModificator="$_rowModificatorMixin_rowModificator_account"
                  :isRight="true"
                  :maxHeight="200"
                  class="width-100"
                />
              </div>
            </div>
          </div>
          <!-- End Categories section -->

          <TransitionCollapseHeight>
            <div v-if="showTransferIncomingAmount" class="custom-mb-16">
              <!-- {{ $t("incomingAmount") }} -->
              <input-currency
                v-model="model.transfer_incoming_amount"
                :signed="false"
                :currencyCode="selectedAccountTransfer.currency"
                :error="$v.model.transfer_incoming_amount.$error"
                placeholder="0"
              />
              <div class="state-caution-hint custom-mt-8 custom-mb-0">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>
                  {{ selectedAccount.currency }} →
                  {{ selectedAccountTransfer.currency }}.
                </strong>
                {{ $t("transferMessage") }}
              </div>
            </div>
          </TransitionCollapseHeight>

          <!-- Start Contractor section -->
          <div class="custom-mb-16">
            <div v-if="!showContractorInput">
              {{ $t("specify") }}
              <a @click.prevent="showContractorInput = true" href="#">{{
                transactionType === "expense" ? $t("recipient") : $t("payee")
              }}</a>
            </div>
            <div v-if="showContractorInput && transactionType !== 'transfer'">
              <InputContractor
                :defaultRequest="`category_id/${model.category_id}`"
                :focus="true"
                @newContractor="
                  name => {
                    model.contractor = name;
                    model.contractor_contact_id = null;
                  }
                "
                @changeContractor="
                  id => {
                    model.contractor = null;
                    model.contractor_contact_id = id;
                  }
                "
              />
            </div>
          </div>
          <!-- End Contractor section -->

          <!-- Start Desc section -->
          <div class="custom-mb-16">
            <textarea
              v-model="model.description"
              class="width-100"
              rows="3"
              style="resize: none; height: auto"
              :placeholder="$t('desc')"
            ></textarea>
            <div
              v-if="
                isModeUpdate &&
                  transaction.external_source_info &&
                  transaction.external_source_info.entity_url
              "
              class="custom-mt-8 flexbox middle space-8"
            >
              <span
                v-if="transaction.external_source_info.entity_icon"
                class="icon userpic size-20"
              >
                <img
                  :src="transaction.external_source_info.entity_icon"
                  alt=""
                />
              </span>
              <a
                :href="transaction.external_source_info.entity_url"
                target="_blank"
                class="small"
                >{{ transaction.external_source_info.entity_name }}</a
              >
            </div>
          </div>
          <!-- End Desc section -->

          <!-- Start ApplyTo section -->
          <div v-if="isModeUpdate && transaction.repeating_id">
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
            <div class="custom-mb-8">
              <label>
                <span class="wa-radio">
                  <input
                    type="radio"
                    :value="true"
                    v-model="model.apply_to_all_in_future"
                  />
                  <span></span>
                </span>
                <span class="small custom-ml-4">{{
                  $t("applyTo.list[1]")
                }}</span>
              </label>
            </div>
            <span class="badge gray squared small">{{ repeatingInfo }}</span>
          </div>
          <!-- End ApplyTo section -->

          <!-- Start Repeat section -->
          <div v-if="!(isModeUpdate && transaction.repeating_id)">
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
          <!-- End Repeat section -->

          <!-- Start howOften section -->
          <TransitionCollapseHeight>
            <div v-if="model.is_repeating" class="custom-mt-16">
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
          </TransitionCollapseHeight>
          <!-- End howOften section -->

          <!-- Start howOften Interval section -->
          <TransitionCollapseHeight>
            <div
              v-if="model.is_repeating && model.repeating_interval === 'custom'"
              class="custom-mt-16"
            >
              <span class="small">{{ $t("howOften.every") }}</span>
              <input
                v-model.number="model.repeating_frequency"
                :class="{
                  'state-error': $v.model.repeating_frequency.$error
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
          </TransitionCollapseHeight>
          <!-- End howOften Interval section -->

          <!-- Start endRepeat section -->
          <TransitionCollapseHeight>
            <div v-if="model.is_repeating" class="custom-mt-16">
              <div class="wa-select small solid">
                <select v-model="model.repeating_end_type">
                  <option value="never">{{ $t("endRepeat.list[0]") }}</option>
                  <option value="after">{{ $t("endRepeat.list[1]") }}</option>
                  <option value="ondate">{{ $t("endRepeat.list[2]") }}</option>
                </select>
              </div>
            </div>
          </TransitionCollapseHeight>
          <!-- End endRepeat section -->

          <!-- Start endRepeat Date section -->
          <TransitionCollapseHeight>
            <div
              v-if="model.is_repeating && model.repeating_end_type !== 'never'"
              class="custom-mt-16"
            >
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
                    'state-error': $v.model.repeating_end_after.$error
                  }"
                />
                <span class="small custom-ml-8">{{
                  $t("endRepeat.occurrences")
                }}</span>
              </div>
            </div>
          </TransitionCollapseHeight>
          <!-- End endRepeat Date section -->
        </div>
      </div>
    </div>

    <div class="dialog-footer">
      <div class="flexbox">
        <div class="flexbox space-12 wide">
          <button
            @click="submit"
            :disabled="controlsDisabled"
            :class="{
              'c-button-add-expense': transactionType === 'expense',
              'c-button-add-income': transactionType === 'income'
            }"
            :style="
              selectedCategory && {
                'background-color': selectedCategory.color
              }
            "
            class="button"
          >
            {{
              isModeUpdate
                ? model.apply_to_all_in_future
                  ? $t("updateAll")
                  : $t("update")
                : $t("add")
            }}
          </button>
          <button @click="close" class="button light-gray">
            {{ $t("cancel") }}
          </button>
        </div>
        <button
          v-if="isModeUpdate"
          @click="remove"
          :disabled="controlsDisabled"
          class="button red outlined"
        >
          <span>{{
            model.apply_to_all_in_future ? $t("deleteAll") : $t("delete")
          }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import {
  required,
  requiredIf,
  integer,
  minValue
} from 'vuelidate/lib/validators'
import InputCurrency from '@/components/Inputs/InputCurrency'
import InputContractor from '@/components/Inputs/InputContractor'
import DateField from '@/components/Inputs/InputDate'
import DropdownWa from '@/components/Inputs/DropdownWa'
import TransitionCollapseHeight from '@/components/Transitions/TransitionCollapseHeight'
import rowModificatorMixin from '@/mixins/rowModificatorMixin.js'
export default {
  props: {
    transaction: {
      type: Object
    },

    defaultCategoryType: {
      type: String,
      default: 'income'
    },

    offOnbadge: {
      type: Boolean,
      default: false
    }
  },

  mixins: [rowModificatorMixin],

  components: {
    InputCurrency,
    InputContractor,
    DateField,
    DropdownWa,
    TransitionCollapseHeight
  },

  data () {
    return {
      transactionType: '',
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
      custom_interval: 'month',
      controlsDisabled: false,
      showContractorInput: false
    }
  },

  validations: {
    model: {
      amount: {
        required,
        minValue: minValue(1)
      },
      date: {
        required
      },
      account_id: {
        required
      },
      category_id: {
        required,
        mustBeTheSameTransactionType: function () {
          return this.selectedCategory?.type === this.transactionType
        }
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
          return this.transactionType === 'transfer'
        })
      },
      transfer_incoming_amount: {
        required: requiredIf(function () {
          return this.showTransferIncomingAmount
        }),
        minValue: function (value) {
          return this.showTransferIncomingAmount ? value > 0 : true
        }
      }
    }
  },

  computed: {
    ...mapState('account', ['accounts']),
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

    showTransferIncomingAmount () {
      return (
        this.transactionType === 'transfer' &&
        this.selectedAccount &&
        this.selectedAccountTransfer &&
        this.selectedAccount.currency !== this.selectedAccountTransfer.currency
      )
    },

    tippyContent () {
      return this.$t(
        `createdBy.${this.transaction.update_datetime ? 'edited' : 'normal'}`,
        {
          username: `${this.transaction.create_contact.firstname} ${this.transaction.create_contact.lastname}`,
          createDate: this.$moment(this.transaction.create_datetime).format(
            'LLL'
          ),
          ...(this.transaction.update_datetime && {
            updateDate: this.$moment(this.transaction.update_datetime).format(
              'LLL'
            )
          })
        }
      )
    },

    repeatingInfo () {
      return this.$tc(
        `repeatingInfo.interval.${this.transaction.repeating_data.interval}`,
        this.transaction.repeating_data.frequency,
        {
          frequency: this.transaction.repeating_data.frequency
        }
      )
    }
  },

  watch: {
    'model.repeating_interval' (val) {
      if (val !== 'custom') this.model.repeating_frequency = 1
    },
    'model.account_id' () {
      this.model.transfer_account_id = null
      this.model.transfer_incoming_amount = null
    },
    'model.transfer_account_id' () {
      this.model.transfer_incoming_amount = null
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

    this.transactionType =
      this.selectedCategory?.type || this.defaultCategoryType

    if (this.transactionType === 'transfer') {
      this.model.category_id = -1312
    }

    // is_onbadge prop manipulations
    if (!this.isModeUpdate) {
      // auto set is_onbadge if future date or repeating
      this.$watch(
        vm => [vm.model.date, vm.model.is_repeating],
        () => {
          this.model.is_onbadge =
            this.$moment(this.model.date).isAfter() || this.model.is_repeating
        }
      )
    } else {
      // switch off is_onbadge
      if (this.offOnbadge) {
        this.model.is_onbadge = false
      }
    }
  },

  methods: {
    submit (event) {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        this.controlsDisabled = true
        const model = { ...this.model }
        if (model.repeating_interval === 'custom') {
          model.repeating_interval = this.custom_interval
        }
        if (!this.showTransferIncomingAmount) {
          model.transfer_incoming_amount = this.model.amount
        }

        this.$store
          .dispatch('transaction/update', model)
          .then(() => {
            if (event.shiftKey) {
              this.$parent.$emit('reOpen')
            } else {
              this.close()
            }
          })
          .finally(() => {
            this.controlsDisabled = false
          })
      }
    },

    remove () {
      if (confirm(this.$t('transactionDeleteWarning'))) {
        this.controlsDisabled = true
        this.$store
          .dispatch('transaction/delete', {
            id: this.model.id,
            all_repeating: this.model.apply_to_all_in_future
          })
          .then(() => {
            this.close()
          })
          .finally(() => {
            this.controlsDisabled = false
          })
      }
    },

    close () {
      this.$parent.$emit('close')
    }
  }
}
</script>
