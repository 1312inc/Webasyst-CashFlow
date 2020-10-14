<template>
  <div>
    <h2 style="margin-bottom:2rem;">
      {{ isModeUpdate ? "Обновить транзакцию" : "Добавить транзакцию" }}
    </h2>

    <div class="fields" style="margin-bottom:2rem;">
      <div v-if="isModeUpdate" class="field">
        <div class="name for-input">Применить к</div>
        <div class="value">
          <div>
            <label>
              <span class="wa-radio">
                <input
                  type="radio"
                  :value="false"
                  v-model="model.apply_to_all_in_future"
                />
                <span></span>
              </span>
              Только для этой транзакции
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
            Этой и последующих
          </label>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">Сумма</div>
        <div class="value">
          <input
            v-model="model.amount"
            :class="{ 'state-error': $v.model.amount.$error }"
            type="text"
          />
        </div>
      </div>

      <div v-if="!isModeUpdate" class="field">
        <div class="name for-input">Повторять</div>
        <div class="value">
          <span
            @click="model.repeating.frequency = !model.repeating.frequency"
            class="switch"
            :class="{ 'is-active': model.repeating.frequency }"
          >
            <!-- <input type="checkbox" v-model="model.repeating.frequency" /> -->
            <span class="switch-toggle"></span>
          </span>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">
          {{ model.repeating.frequency ? "Повторять с" : "Дата" }}
        </div>
        <div class="value">
          <div class="input-with-inner-icon left">
            <input
              v-model="model.date"
              :class="{ 'state-error': $v.model.date.$error }"
              type="text"
            />
            <span class="icon"><i class="fas fa-calendar"></i></span>
          </div>
        </div>
      </div>

      <div v-if="!isModeUpdate && model.repeating.frequency" class="field">
        <div class="name for-input">Как часто</div>
        <div class="value">
          <div class="wa-select">
            <select v-model="model.repeating.interval">
              <option value="month">Каждый месяц</option>
              <option value="day">Каждый день</option>
              <option value="week">Каждую неделю</option>
              <option value="year">Каждый год</option>
            </select>
          </div>
        </div>
      </div>

      <div v-if="!isModeUpdate && model.repeating.frequency" class="field">
        <div class="name for-input">Закончить</div>
        <div class="value">
          <div class="wa-select">
            <select v-model="model.repeating.end_type">
              <option value="never">Никогда</option>
              <option value="after">Количество раз</option>
              <option value="ondate">По дате</option>
            </select>
          </div>

          <div v-if="model.repeating.end_type === 'ondate'">
            <div class="input-with-inner-icon left">
              <input v-model="model.repeating.end_ondate" type="text" />
              <span class="icon"><i class="fas fa-calendar"></i></span>
            </div>
          </div>

          <div v-if="model.repeating.end_type === 'after'">
            <input
              v-model="model.repeating.end_after"
              type="text"
              class="shorter"
            />
            <span>раз</span>
          </div>
        </div>
      </div>

      <div class="field">
        <div class="name for-input">Аккаунт</div>
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
        <div class="name for-input">Категория</div>
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
        <div class="name for-input">Контрактор</div>
        <div class="value">
          <input v-model="model.contractor_contact_id" type="text" />
        </div>
      </div>

      <div class="field">
        <div class="name for-input">Описание</div>
        <div class="value">
          <textarea
            v-model="model.description"
            class="wide"
            rows="4"
            style="resize: none; height: auto"
          ></textarea>
        </div>
      </div>
    </div>

    <div class="flexbox">
      <div class="flexbox space-1rem wide">
        <button @click="submit" class="button purple">
          {{ isModeUpdate ? "Изменить" : "Добавить" }}
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
import { required } from 'vuelidate/lib/validators'
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
          frequency: false,
          interval: 'month',
          end_type: 'never',
          end_after: 0,
          end_ondate: ''
        },
        apply_to_all_in_future: false
      }
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

  created () {
    if (this.transaction) {
      for (const prop in this.model) {
        this.model[prop] = this.transaction[prop] || this.model[prop]
      }
    }
  },

  methods: {
    submit () {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        this.$store
          .dispatch('transaction/update', this.model)
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
