<template>
  <div
    class="dialog-body"
    style="width: 750px"
  >
    <div class="dialog-header">
      <div class="flexbox middle wrap-mobile">
        <div class="wide flexbox middle">
          <h2
            v-if="transactionType === 'transfer' && !isModeUpdate"
            class="custom-mb-0"
          >
            {{ $t("newTransfer") }}
          </h2>
          <h2
            v-else
            class="custom-mb-0"
          >
            {{ isModeUpdate ? $t("updateTransaction") : $t("addTransaction") }}
          </h2>
          <span
            v-if="isModeUpdate && transaction.repeating_id"
            class="custom-ml-8 larger"
            :title="$t('repeatingTran')"
          >
            <i class="fas fa-redo opacity-30" />
          </span>
        </div>
        <div v-if="isModeUpdate && transaction.create_contact_id">
          <span
            v-wa-tippy="tippyContent"
            class="icon userpic size-32"
          >
            <img
              :src="transaction.create_contact.userpic"
              alt=""
            >
          </span>
        </div>
        <!-- Start Toggle type section -->
        <div
          v-if="!isModeUpdate"
          class="toggle custom-mt-8"
        >
          <span
            v-for="(type, i) in [
              'income',
              'expense',
              ...(accounts.length > 1 ? ['transfer'] : [])
            ]"
            :key="i"
            :class="{ selected: transactionType === type }"
            @click="transactionType = type"
          >{{ $t(type)
          }}</span>
        </div>
        <!-- End Toggle type section -->
      </div>
    </div>

    <div class="dialog-content">
      <div class="flexbox space-24 wrap-mobile reverse-mobile">
        <div
          class="custom-mt-24-mobile"
          style="flex: 0;"
        >
          <div class="custom-mb-16 c-inline-calendar">
            <DateField
              v-model="model.date"
              :inline="true"
              :class="{ 'state-error': $v.model.date.$error }"
              :disabled="model.apply_to_all_in_future"
            />
          </div>

          <div class="custom-mb-16">
            <i18n path="transactionDate">
              <template #date>
                <strong>{{ $moment(model.date).format("LL") }}</strong>
              </template>
            </i18n>
          </div>

          <div>
            <label @click.prevent="model.is_onbadge = !model.is_onbadge">
              <span class="wa-checkbox">
                <input
                  type="checkbox"
                  :checked="model.is_onbadge"
                >
                <span>
                  <span class="icon">
                    <i class="fas fa-check" />
                  </span>
                </span>
              </span>
              {{ $t("notifyMe") }} <span v-if="model.is_onbadge"><span class="badge smaller">1</span>&nbsp;</span>
              <span v-wa-tippy="$t('notifyMeAlert')">
                <i class="fas fa-info-circle opacity-40" />
              </span>
            </label>
          </div>

          <div
            v-if="(new Date(model.date)).getTime() > new Date().getTime() || model.is_repeating"
            class="custom-mt-16"
          >
            <label @click.prevent="model.is_self_destruct_when_due = !model.is_self_destruct_when_due">
              <span class="wa-checkbox">
                <input
                  type="checkbox"
                  :checked="model.is_self_destruct_when_due"
                >
                <span>
                  <span class="icon">
                    <i class="fas fa-check" />
                  </span>
                </span>
              </span>
              {{ $t("selfDestructLabel") }}
              <span v-wa-tippy="$t('selfDestructText')">
                <i class="fas fa-info-circle opacity-40" />
              </span>
            </label>
          </div>
        </div>
        <div class="wide">
          <!-- Start Currency Input section -->
          <div class="custom-mb-16">
            <input-currency
              v-model="model.amount"
              :signed="false"
              :category-id="model.category_id"
              :account-id="model.account_id"
              :error="$v.model.amount.$error"
              :transaction-type="transactionType"
              :focused="true"
              class="larger"
              placeholder="0"
              @keyEnter="submit"
            />
          </div>
          <!-- End Currency Input section -->

          <!-- Start Categories section -->
          <div class="flexbox middle custom-mb-16">
            <div
              v-if="transactionType !== 'transfer'"
              class="width-50"
            >
              <DropdownWa
                v-model="model.category_id"
                :error="$v.model.category_id.$error"
                :label="$t('category')"
                :items="getCategoryByType(transactionType)"
                value-prop-name="id"
                :row-modificator="$_rowModificatorMixin_rowModificator_category"
                :max-height="200"
                class="width-100"
              />
            </div>
            <div
              v-if="transactionType !== 'transfer'"
              class="custom-px-8 gray"
              :style="transactionType === 'expense'
                ? 'transform: rotate(180deg);'
                : ''
              "
            >
              <i class="fas fa-arrow-right" />
            </div>

            <div
              :class="transactionType === 'transfer' && isModeUpdate
                ? 'width-100'
                : 'width-50'
              "
            >
              <DropdownWa
                v-model="model.account_id"
                :error="$v.model.account_id.$error"
                :label="transactionType === 'transfer' && isModeUpdate ? $t('account') :
                  transactionType === 'transfer' ||
                  transactionType === 'expense'
                    ? $t('fromAccount')
                    : $t('toAccount')
                "
                :items="accounts"
                value-prop-name="id"
                :row-modificator="$_rowModificatorMixin_rowModificator_account"
                :is-right="transactionType !== 'transfer'"
                :max-height="200"
                class="width-100"
              />
            </div>
            <div
              v-if="transactionType === 'transfer' && !isModeUpdate"
              class="custom-px-8 gray"
            >
              <i class="fas fa-arrow-right" />
            </div>
            <div
              v-if="transactionType === 'transfer' && !isModeUpdate"
              class="width-50"
            >
              <DropdownWa
                v-model="model.transfer_account_id"
                :error="$v.model.transfer_account_id.$error"
                :label="$t('toAccount')"
                :items="accountsTransfer"
                value-prop-name="id"
                :row-modificator="$_rowModificatorMixin_rowModificator_account"
                :is-right="true"
                :max-height="200"
                class="width-100"
              />
            </div>
          </div>
          <!-- End Categories section -->

          <TransitionCollapseHeight>
            <div
              v-if="showTransferIncomingAmount"
              class="custom-mb-16"
            >
              <!-- {{ $t("incomingAmount") }} -->
              <input-currency
                v-model="model.transfer_incoming_amount"
                :signed="false"
                :currency-code="selectedAccountTransfer.currency"
                :error="$v.model.transfer_incoming_amount.$error"
                placeholder="0"
              />
              <div class="state-caution-hint custom-mt-8 custom-mb-0">
                <i class="fas fa-exclamation-triangle" />
                <strong>
                  {{ selectedAccount.currency }} →
                  {{ selectedAccountTransfer.currency }}.
                </strong>
                {{ $t("transferMessage") }}
              </div>
            </div>
          </TransitionCollapseHeight>

          <!-- Start Contractor section -->
          <div
            v-if="transactionType !== 'transfer'"
            class="custom-mb-16"
          >
            <div v-if="!showContractorInput">
              {{ $t("specify") }}
              <a @click.prevent="showContractorInput = true">{{
                transactionType === "expense" ? $t("recipient") : $t("payee")
              }}{{ appState.shopscriptInstalled ? ` ${$t("orOrder")}` : '' }}</a>
            </div>
            <div v-if="showContractorInput">
              <InputContractor
                :default-request="`category_id/${model.category_id}`"
                :default-contractor="model.contractor_contact_id ? { id: model.contractor_contact_id, ...model.contractor_contact } : null"
                @newContractor="name => {
                  model.contractor = name;
                }"
                @changeContractor="id => {
                  model.contractor_contact_id = id;
                }"
              />

              <!-- Start Link with SS Order section -->
              <div
                v-if="model.external.source === 'shop'"
                class="custom-my-16 flexbox middle space-8"
              >
                <div class="state-with-inner-icon left width-100">
                  <input
                    v-model="model.external.id"
                    type="text"
                    class="width-100"
                    :placeholder="$t('orderNumber')"
                    @input.prevent="(e) => { model.external.id = e.target.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1') || null }"
                  >
                  <span
                    class="icon"
                    style="opacity: 1;"
                  >
                    <img
                      :src="`${appState.baseStaticUrl}img/shop.svg`"
                      alt=""
                    >
                  </span>
                </div>
                <a
                  class="icon gray"
                  @click.prevent="model.external.id = null"
                ><i class="fas fa-times" /></a>
              </div>
              <!-- End Link with SS Order section -->
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
            />
            <div
              v-if="isModeUpdate"
              class="custom-mt-8"
            >
              <div
                v-if="transaction.external_source_info"
                class="flexbox middle space-8"
              >
                <span
                  v-if="transaction.external_source_info.entity_icon"
                  class="icon size-20"
                >
                  <img
                    :src="transaction.external_source_info.entity_icon"
                    alt=""
                  >
                </span>
                <div v-else-if="transaction.external_source_info.glyph">
                  <i
                    :class="transaction.external_source_info.glyph"
                    :style="`color: ${transaction.external_source_info.color}`"
                  />
                </div>
                <a
                  v-if="transaction.external_source !== 'shop'"
                  :href="transaction.external_source_info.entity_url"
                  target="_blank"
                >{{ transaction.external_source_info.entity_name }}</a>
                <RouterLink
                  v-else
                  :to="{ name: 'Order', params: { id: transaction.external_source_info.id } }"
                >
                  {{ transaction.external_source_info.entity_name }}
                </RouterLink>
              </div>
              <div
                v-if="model.contractor_contact"
                class="flexbox middle space-8 custom-mt-8"
              >
                <span class="icon size-20">
                  <img
                    :src="model.contractor_contact.userpic"
                    class="userpic"
                    alt=""
                  >
                </span>
                <RouterLink
                  v-slot="{ href, navigate, isActive }"
                  :to="{ name: 'Contact', params: { id: model.contractor_contact_id } }"
                  custom
                >
                  <a
                    :href="href"
                    @click.prevent="() => {
                      isActive ? close() : navigate({ name: 'Contact', params: { id: model.contractor_contact_id } })
                    }"
                  >{{ model.contractor_contact.name }}</a>
                </RouterLink>
              </div>
            </div>
          </div>
          <!-- End Desc section -->

          <!-- Start ApplyTo section -->
          <div v-if="isModeUpdate && transaction.repeating_id">
            <div class="custom-mb-8">
              <label>
                <span class="wa-radio">
                  <input
                    v-model="model.apply_to_all_in_future"
                    type="radio"
                    :value="false"
                  >
                  <span />
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
                    v-model="model.apply_to_all_in_future"
                    type="radio"
                    :value="true"
                  >
                  <span />
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
                :class="{ selected: !model.is_repeating }"
                @click="model.is_repeating = false"
              >{{ $t("oneTime")
              }}</span>
              <span
                :class="{ selected: model.is_repeating }"
                @click="model.is_repeating = true"
              >{{ $t("repeating")
              }}</span>
            </div>
          </div>
          <!-- End Repeat section -->

          <!-- Start howOften section -->
          <TransitionCollapseHeight>
            <div
              v-if="model.is_repeating"
              class="custom-mt-16"
            >
              <div class="wa-select small solid">
                <select v-model="model.repeating_interval">
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
                  <option value="custom">
                    {{ $t("howOften.list[4]") }}
                  </option>
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
              >
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
            <div
              v-if="model.is_repeating"
              class="custom-mt-16"
            >
              <div class="wa-select small solid">
                <select v-model="model.repeating_end_type">
                  <option value="never">
                    {{ $t("endRepeat.list[0]") }}
                  </option>
                  <option value="after">
                    {{ $t("endRepeat.list[1]") }}
                  </option>
                  <option value="ondate">
                    {{ $t("endRepeat.list[2]") }}
                  </option>
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
                <span class="icon"><i class="fas fa-calendar" /></span>
              </div>

              <div v-if="model.repeating_end_type === 'after'">
                <input
                  v-model.number="model.repeating_end_after"
                  type="text"
                  class="shortest small number"
                  :class="{
                    'state-error': $v.model.repeating_end_after.$error
                  }"
                >
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
            :disabled="controlsDisabled"
            :class="{
              'c-button-add-expense': transactionType === 'expense',
              'c-button-add-income': transactionType === 'income'
            }"
            :style="selectedCategory && {
              'background-color': selectedCategory.color
            }
            "
            class="button"
            @click="submit"
          >
            {{
              isModeUpdate
                ? model.apply_to_all_in_future
                  ? $t("updateAll")
                  : $t("update")
                : $t("add")
            }}
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
          class="button red outlined"
          @click="remove"
        >
          <span>{{
            model.apply_to_all_in_future ? $t("deleteAll") : $t("delete")
          }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { appState } from '@/utils/appState'
</script>

<script>
import { mapState, mapGetters } from 'vuex'
import {
  required,
  requiredIf,
  integer
} from 'vuelidate/dist/validators.min.js'
import InputCurrency from '@/components/Inputs/InputCurrency'
import InputContractor from '@/components/Inputs/InputContractor'
import DateField from '@/components/Inputs/InputDate'
import DropdownWa from '@/components/Inputs/DropdownWa'
import TransitionCollapseHeight from '@/components/Transitions/TransitionCollapseHeight'
import rowModificatorMixin from '@/mixins/rowModificatorMixin.js'
import entityPageMixin from '@/mixins/entityPageMixin'
import api from '../../plugins/api'

export default {

  components: {
    InputCurrency,
    InputContractor,
    DateField,
    DropdownWa,
    TransitionCollapseHeight
  },

  mixins: [rowModificatorMixin, entityPageMixin],
  props: {
    transaction: {
      type: Object
    },

    defaultCategoryType: {
      type: String,
      default: 'income'
    },

    defaultDate: {
      type: String
    },

    offOnbadge: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      transactionType: '',
      model: {
        id: null,
        amount: null,
        date: null,
        account_id: null,
        category_id: null,
        contractor: null,
        contractor_contact: null,
        contractor_contact_id: null,
        description: '',
        external: {
          source: this.transaction?.external_source || (appState.shopscriptInstalled ? 'shop' : null),
          id: this.transaction?.external_id || null
        },
        is_onbadge: false,
        is_repeating: false,
        repeating_frequency: 1,
        repeating_interval: 'month',
        repeating_end_type: 'never',
        repeating_end_after: null,
        repeating_end_ondate: null,
        transfer_account_id: null,
        transfer_incoming_amount: null,
        apply_to_all_in_future: false,
        is_self_destruct_when_due: false
      },
      custom_interval: 'month',
      controlsDisabled: false,
      showContractorInput: false
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
          return this.transactionType === 'transfer' && !this.isModeUpdate
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
    },
    'model.date' (val) {
      if ((new Date(val)).getTime() <= new Date().getTime()) {
        this.model.is_self_destruct_when_due = false
      }
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
        if (prop in this.transaction) {
          this.model[prop] = this.transaction[prop]
        }
      }
      this.model.amount = `${Math.abs(this.model.amount)}`
    }

    this.transactionType =
      this.selectedCategory?.type || this.defaultCategoryType

    if (this.defaultDate) {
      this.model.date = this.defaultDate
    }

    this.$watch('transactionType', (val) => {
      if (val === 'transfer') {
        this.model.category_id = -1312
      }
    }, { immediate: true })

    if (this.model.contractor_contact?.name || this.model.external.id) {
      this.showContractorInput = true
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
        this.model.date = this.$moment().format('YYYY-MM-DD')
        this.model.is_onbadge = false
      }
    }
  },

  methods: {
    async submit (event) {
      this.$v.$touch()
      if (this.$v.$invalid) return

      this.controlsDisabled = true

      const model = { ...this.model }
      if (model.repeating_interval === 'custom') {
        model.repeating_interval = this.custom_interval
      }
      if (!this.showTransferIncomingAmount) {
        model.transfer_incoming_amount = this.model.amount
      }

      if (!model.external.id && model.external.source === 'shop') {
        model.external.source = null
      }
      if (model.external.id && model.external.source === 'shop') {
        try {
          const { data } = await api.get(`cash.system.getExternalEntity?source=shop&id=${model.external.id}`)
          if (data.entity_id) {
            model.external.id = data.entity_id
          }
        } catch (e) {
          this.$store.commit('errors/error', {
            title: 'error.api',
            method: '',
            message: this.$t('orderNotFound')
          })
          this.controlsDisabled = false
          return
        }
      }

      this.$store
        .dispatch('transaction/update', model)
        .then(() => {
          if (event.shiftKey) {
            this.$parent.$emit('reOpen')
          } else {
            this.close()
            // scroll top to see the new transaction
            if (!this.model.id) {
              window.scrollTo({
                top: 0,
                behavior: 'smooth'
              })
            }
          }
        })
        .catch((e) => {})
        .finally(() => {
          this.controlsDisabled = false
        })
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
