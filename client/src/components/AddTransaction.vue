<template>
  <div>
    <div class="flexbox custom-mb-32">
      <div class="wide">
        <h2>
          {{ isModeUpdate ? $t("updateTransaction") : $t("addTransaction") }}
        </h2>
      </div>
      <div v-if="isModeUpdate">#{{ transaction.id }}</div>
    </div>

    <div class="fields custom-mb-32">
      <div v-if="isModeUpdate" class="field">
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
              {{ $t("applyTo.list[0]") }}
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
            {{ $t("applyTo.list[1]") }}
          </label>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ $t("ammount") }}
        </div>
        <div class="value">
          <input
            v-model="model.amount"
            :class="{ 'state-error': $v.model.amount.$error }"
            type="text"
          />
        </div>
      </div>

      <div v-if="!isModeUpdate" class="field">
        <div class="name for-input">
          {{ $t("repeat") }}
        </div>
        <div class="value">
          <span
            @click="
              model.repeating.frequency = model.repeating.frequency ? 0 : 1
            "
            class="switch"
            :class="{ 'is-active': model.repeating.frequency }"
          >
            <span class="switch-toggle"></span>
          </span>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ model.repeating.frequency ? $t("repeatFrom") : $t("date") }}
        </div>
        <div class="value">
          <div class="input-with-inner-icon left">
            <input
              v-model="model.date"
              :class="{ 'state-error': $v.model.date.$error }"
              type="text"
              ref="date"
            />
            <span class="icon"><i class="fas fa-calendar"></i></span>
          </div>
        </div>
      </div>

      <div v-if="!isModeUpdate && model.repeating.frequency" class="field">
        <div class="name for-input">
          {{ $t("howOften.name") }}
        </div>
        <div class="value">
          <div class="wa-select">
            <select v-model="model.repeating.interval">
              <option value="month">{{ $t("howOften.list[0]") }}</option>
              <option value="day">{{ $t("howOften.list[1]") }}</option>
              <option value="week">{{ $t("howOften.list[2]") }}</option>
              <option value="year">{{ $t("howOften.list[3]") }}</option>
              <option value="custom">{{ $t("howOften.list[4]") }}</option>
            </select>
          </div>

          <div v-if="model.repeating.interval === 'custom'" class="tw-mt-4">
            every
            <input
              v-model.number="model.repeating.frequency"
              type="text"
              class="shorter custom-ml-8"
            />
            <div class="wa-select custom-ml-8">
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

      <div v-if="!isModeUpdate && model.repeating.frequency" class="field">
        <div class="name for-input">
          {{ $t("endRepeat.name") }}
        </div>
        <div class="value">
          <div class="wa-select">
            <select v-model="model.repeating.end_type">
              <option value="never">{{ $t("endRepeat.list[0]") }}</option>
              <option value="after">{{ $t("endRepeat.list[1]") }}</option>
              <option value="ondate">{{ $t("endRepeat.list[2]") }}</option>
            </select>
          </div>

          <div v-if="model.repeating.end_type === 'ondate'" class="tw-mt-4">
            <div class="input-with-inner-icon left">
              <input
                v-model="model.repeating.end_ondate"
                type="text"
                ref="endDate"
              />
              <span class="icon"><i class="fas fa-calendar"></i></span>
            </div>
          </div>

          <div v-if="model.repeating.end_type === 'after'" class="tw-mt-4">
            <input
              v-model.number="model.repeating.end_after"
              type="text"
              class="shorter"
              :class="{ 'state-error': $v.model.repeating.end_after.$error }"
            />
            <span class="tw-ml-2">{{ $t("endRepeat.occurrences") }}</span>
          </div>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ $t("account") }}
        </div>
        <div class="value">
          <div class="wa-select">
            <select
              v-model="model.account_id"
              :class="{ 'state-error': $v.model.account_id.$error }"
            >
              <option
                :value="account.id"
                v-for="account in accounts"
                :key="account.id"
              >
                {{ account.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ $t("category") }}
        </div>
        <div class="value">
          <div class="wa-select">
            <select
              v-model="model.category_id"
              :class="{ 'state-error': $v.model.category_id.$error }"
            >
              <option
                :value="category.id"
                v-for="category in categories"
                :key="category.id"
              >
                {{ category.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ $t("contractor") }}
        </div>
        <div class="value">
          <input v-model="model.contractor_contact_id" type="text" />
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ $t("desc") }}
        </div>
        <div class="value">
          <textarea
            v-model="model.description"
            class="wide"
            rows="4"
            style="resize: none; height: auto"
            :placeholder="$t('optional')"
          ></textarea>
        </div>
      </div>
    </div>

    <div class="flexbox">
      <div class="flexbox space-1rem wide">
        <button @click="submit" class="button purple">
          {{ isModeUpdate ? $t("update") : $t("add") }}
        </button>
        <button @click="close" class="button light-gray">
          {{ $t("cancel") }}
        </button>
      </div>
      <button v-if="isModeUpdate" @click="remove" class="button red">
        {{ $t("delete") }}
      </button>
    </div>
  </div>
</template>

<script>
import { required, integer, numeric } from 'vuelidate/lib/validators'
import { locale } from '@/plugins/locale'
import flatpickr from 'flatpickr'
import { Russian } from 'flatpickr/dist/l10n/ru.js'
export default {
  props: {
    transaction: {
      type: Object
    },

    defaultCategoryType: {
      type: String
    }
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
        description: '',
        repeating: {
          frequency: 0,
          interval: 'month',
          end_type: 'never',
          end_after: 0,
          end_ondate: ''
        },
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
      repeating: {
        end_after: {
          integer
        }
      }
    }
  },

  computed: {
    isModeUpdate () {
      return this.transaction
    },

    accounts () {
      return this.$store.state.account.accounts
    },

    categories () {
      const cat = this.$store.getters['category/getById'](
        this.model.category_id
      )
      return this.$store.state.category.categories.filter(
        (c) => c.type === (cat ? cat.type : this.defaultCategoryType)
      )
    }
  },

  watch: {
    'model.repeating.end_type' () {
      if (this.flatpickr2) this.flatpickr2.destroy()
      this.$nextTick(() => {
        if (this.$refs.endDate) {
          this.flatpickr2 = flatpickr(this.$refs.endDate, {
            locale: locale === 'ru_RU' ? Russian : 'en'
          })
        }
      })
    },

    'model.repeating.interval' (val) {
      if (val !== 'custom') this.model.repeating.frequency = 1
    }
  },

  created () {
    if (this.transaction) {
      for (const prop in this.model) {
        this.model[prop] = this.transaction[prop] || this.model[prop]
      }
    }
  },

  mounted () {
    this.flatpickr = flatpickr(this.$refs.date, {
      locale: locale === 'ru_RU' ? Russian : 'en'
    })
  },

  destroyed () {
    if (this.flatpickr) this.flatpickr.destroy()
    if (this.flatpickr2) this.flatpickr2.destroy()
  },

  methods: {
    submit () {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        const model = { ...this.model }
        if (model.repeating.interval === 'custom') {
          model.repeating.interval = this.custom_interval
        }

        this.$store
          .dispatch('transaction/update', model)
          .then(() => {
            this.$noty.success('Транзакция успешно обновлена')
            this.$parent.$emit('close')
          })
          .catch(() => {
            this.$noty.error('Oops, something went wrong!')
          })
      }
    },

    remove () {
      this.$store
        .dispatch('transaction/delete', this.model.id)
        .then(() => {
          this.$noty.success('Транзакция успешно удалена')
          this.$parent.$emit('close')
        })
        .catch(() => {
          this.$noty.error('Oops, something went wrong!')
        })
    },

    close () {
      this.$parent.$emit('close')
    }
  }
}
</script>

<style lang="scss">
@import "~flatpickr/dist/flatpickr.css";
</style>
