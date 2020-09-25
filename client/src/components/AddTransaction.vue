<template>
  <div>
    <div class="flex items-center justify-between mb-10">
      <div>
        <div class="text-2xl">
          {{ isModeUpdate ? "Обновить транзакцию" : "Добавить транзакцию" }}
        </div>
      </div>
      <div v-if="isModeUpdate">id: {{ this.model.id }}</div>
    </div>

    <div v-if="isModeUpdate" class="md:flex md:items-center mb-6">
      <div class="md:w-1/3">
        <label
          class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
        >
          Применить к
        </label>
      </div>
      <div class="md:w-2/3">
        <div>
          <label class="inline-flex items-center">
            <input
              type="radio"
              :value="false"
              v-model="model.apply_to_all_in_future"
            />
            <span class="ml-2">Только для этой транзакции</span>
          </label>
        </div>
        <div>
          <label class="inline-flex items-center">
            <input
              type="radio"
              :value="true"
              v-model="model.apply_to_all_in_future"
            />
            <span class="ml-2">Этой и последующих</span>
          </label>
        </div>
      </div>
    </div>

    <div class="md:flex md:items-center mb-6">
      <div class="md:w-1/3">
        <label
          class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
        >
          Сумма
        </label>
      </div>
      <div class="md:w-2/3">
        <input
          v-model="model.amount"
          :class="{ 'border-red-500': $v.model.amount.$error }"
          class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
          type="text"
        />
      </div>
    </div>

    <div v-if="!isModeUpdate">
      <div class="md:flex md:items-center mb-6">
        <div class="md:w-1/3">
          <label
            class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
          >
            Повторять
          </label>
        </div>
        <div class="md:w-2/3">
          <div
            class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in"
          >
            <input
              type="checkbox"
              v-model="model.repeating.frequency"
              class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
            />
            <label
              class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"
            ></label>
          </div>
        </div>
      </div>
    </div>

    <div class="md:flex md:items-center mb-6">
      <div class="md:w-1/3">
        <label
          class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
        >
          {{ model.repeating.frequency ? "Повторять с" : "Дата" }}
        </label>
      </div>
      <div class="md:w-2/3">
        <input
          v-model="model.date"
          :class="{ 'border-red-500': $v.model.date.$error }"
          class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
          type="text"
        />
      </div>
    </div>

    <div v-if="!isModeUpdate && model.repeating.frequency">
      <div class="md:flex md:items-center mb-6">
        <div class="md:w-1/3">
          <label
            class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
          >
            Как часто
          </label>
        </div>
        <div class="md:w-2/3">
          <div class="relative">
            <select
              v-model="model.repeating.interval"
              class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
            >
              <option value="month">Каждый месяц</option>
              <option value="day">Каждый день</option>
              <option value="week">Каждую неделю</option>
              <option value="year">Каждый год</option>
            </select>
            <div
              class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
            >
              <svg
                class="fill-current h-4 w-4"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
              >
                <path
                  d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"
                />
              </svg>
            </div>
          </div>
        </div>
      </div>
      <div class="md:flex md:items-center mb-6">
        <div class="md:w-1/3">
          <label
            class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
          >
            Закончить
          </label>
        </div>
        <div class="md:w-2/3">
          <div class="relative">
            <select
              v-model="model.repeating.end_type"
              class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
            >
              <option value="never">Никогда</option>
              <option value="after">Количество раз</option>
              <option value="ondate">По дате</option>
            </select>
            <div
              class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
            >
              <svg
                class="fill-current h-4 w-4"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
              >
                <path
                  d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"
                />
              </svg>
            </div>
          </div>

          <input
            v-if="model.repeating.end_type === 'ondate'"
            v-model="model.repeating.end_ondate"
            class="mt-4 appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
            type="text"
          />

          <div
            v-if="model.repeating.end_type === 'after'"
            class="mt-4 flex items-center"
          >
            <div class="mr-2">
              <input
                v-model="model.repeating.end_after"
                class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                type="text"
              />
            </div>
            <div>раз</div>
          </div>
        </div>
      </div>
    </div>

    <div class="md:flex md:items-center mb-6">
      <div class="md:w-1/3">
        <label
          class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
        >
          Аккаунт
        </label>
      </div>
      <div class="md:w-2/3">
        <div class="relative">
          <select
            v-model="model.account_id"
            :class="{ 'border-red-500': $v.model.account_id.$error }"
            class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
          >
            <option
              :value="account.id"
              v-for="account in accounts"
              :key="account.id"
            >
              {{ account.name }}
            </option>
          </select>
          <div
            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
          >
            <svg
              class="fill-current h-4 w-4"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
            >
              <path
                d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"
              />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="md:flex md:items-center mb-6">
      <div class="md:w-1/3">
        <label
          class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
        >
          Категория
        </label>
      </div>
      <div class="md:w-2/3">
        <div class="relative">
          <select
            v-model="model.category_id"
            :class="{ 'border-red-500': $v.model.category_id.$error }"
            class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
          >
            <option
              :value="category.id"
              v-for="category in categories"
              :key="category.id"
            >
              {{ category.name }}
            </option>
          </select>
          <div
            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700"
          >
            <svg
              class="fill-current h-4 w-4"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
            >
              <path
                d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"
              />
            </svg>
          </div>
        </div>
      </div>
    </div>

    <div class="md:flex md:items-center mb-6">
      <div class="md:w-1/3">
        <label
          class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
        >
          Контрактор
        </label>
      </div>
      <div class="md:w-2/3">
        <input
          v-model="model.contractor_contact_id"
          class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
          type="text"
        />
      </div>
    </div>

    <div class="md:flex md:items-center mb-6">
      <div class="md:w-1/3">
        <label
          class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
        >
          Описание
        </label>
      </div>
      <div class="md:w-2/3">
        <textarea
          v-model="model.description"
          class="appearance-none block w-full bg-gray-200 text-gray-700 border border-gray-200 rounded py-3 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
        ></textarea>
      </div>
    </div>

    <div class="flex justify-between">
      <div>
        <button @click="submit" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-white text-base bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-700 active:bg-indigo-700 transition duration-150 ease-in-out">
          {{ isModeUpdate ? "Изменить" : "Добавить" }}
        </button>
        или
        <a href="#" @click.prevent="close" class="border-b text-indigo">отменить</a>
      </div>
      <div>
        <button v-if="isModeUpdate" @click="remove" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-medium rounded-md text-indigo-600 text-base border border-indigo-600 focus:outline-none focus:shadow-outline-indigo focus:border-indigo-700 active:bg-indigo-700 transition duration-150 ease-in-out">
          Удалить
        </button>
      </div>
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
      const cat = this.$store.getters['category/getCategoryById'](
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

<style>
/* CHECKBOX TOGGLE SWITCH */
/* @apply rules for documentation, these do not work as inline style */
.toggle-checkbox:checked {
  @apply: right-0 border-green-400;
  right: 0;
  border-color: #68d391;
}
.toggle-checkbox:checked + .toggle-label {
  @apply: bg-green-400;
  background-color: #68d391;
}
</style>
