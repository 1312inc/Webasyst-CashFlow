<template>
  <div>
    <div class="mb-6 text-xl">
      {{ isModeUpdate ? "Обновить транзакцию" : "Добавить транзакцию" }} {{this.model.id}}
    </div>

    <div class="md:flex md:items-center mb-6">
      <div class="md:w-1/3">
        <label
          class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
          for="inline-full-name"
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

    <div class="md:flex md:items-center mb-6">
      <div class="md:w-1/3">
        <label
          class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
          for="inline-full-name"
        >
          Дата
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

    <div class="md:flex md:items-center mb-6">
      <div class="md:w-1/3">
        <label
          class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
          for="inline-full-name"
        >
          Аккаунт
        </label>
      </div>
      <div class="md:w-2/3">
        <div class="relative">
          <select
            v-model="model.account_id"
            class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
          >
            <option :value="account.id" v-for="account in accounts" :key="account.id">{{account.name}}</option>
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
          for="inline-full-name"
        >
          Категория
        </label>
      </div>
      <div class="md:w-2/3">
        <div class="relative">
          <select
            v-model="model.category_id"
            class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
          >
            <option :value="category.id" v-for="category in categories" :key="category.id">{{category.name}}</option>
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
          for="inline-full-name"
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
          for="inline-full-name"
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
        <button class="button" @click="close">Отменить</button>
      </div>
      <div>
        <button v-if="isModeUpdate" class="button mr-4" @click="remove">
          Удалить
        </button>
        <button class="button" @click="submit">
          {{ isModeUpdate ? "Изменить" : "Добавить" }}
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
      type: Object,
      default () {
        return null
      }
    }
  },

  data () {
    return {
      model: {
        id: null,
        amount: null,
        date: null,
        account_id: null,
        category_id: null,
        contractor_contact_id: null,
        description: null,
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
      const cat = this.$store.getters['category/getCategoryById'](this.model.category_id)
      return cat ? this.$store.state.category.categories.filter(c => c.type === cat.type) : this.$store.state.category.categories
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
