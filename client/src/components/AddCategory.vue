<template>
  <div>
    <div class="mb-6 text-2xl">
      {{ isModeUpdate ? "Изменить категорию" : "Добавить категорию" }}
    </div>

    <div class="md:flex md:items-center mb-6">
      <div class="md:w-1/3">
        <label
          class="block text-gray-500 font-bold md:text-right mb-1 md:mb-0 pr-4"
          for="inline-full-name"
        >
          Название
        </label>
      </div>
      <div class="md:w-2/3">
        <input
          v-model="model.name"
          :class="{ 'border-red-500': $v.model.name.$error }"
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
          Тип
        </label>
      </div>
      <div class="md:w-2/3">
        <div class="relative">
          <select
            v-model="model.type"
            :class="{ 'border-red-500': $v.model.type.$error }"
            class="block appearance-none w-full bg-gray-200 border border-gray-200 text-gray-700 py-3 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
          >
            <option value="expense">Расход</option>
            <option value="income">Приход</option>
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
          Цвет
        </label>
      </div>
      <div class="md:w-2/3">
        <CategoryColors />
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
import CategoryColors from '@/components/CategoryColors'
export default {
  props: {
    editedItem: {
      type: Object
    }
  },

  components: {
    CategoryColors
  },

  data () {
    return {
      model: {
        id: null,
        name: '',
        type: '',
        color: '#000000',
        description: ''
      }
    }
  },

  validations: {
    model: {
      name: {
        required
      },
      type: {
        required
      }
    }
  },

  computed: {
    isModeUpdate () {
      return this.editedItem
    }
  },

  created () {
    if (this.editedItem) {
      for (const prop in this.model) {
        this.model[prop] = this.editedItem[prop] || this.model[prop]
      }
    }
  },

  methods: {
    submit () {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        this.$store
          .dispatch('category/update', this.model)
          .then(() => {
            this.$noty.success('Категория успешно обновлена')
            this.$parent.$emit('close')
          })
          .catch(() => {
            this.$noty.error('Oops, something went wrong!')
          })
      }
    },

    remove () {
      this.$store
        .dispatch('category/delete', this.model.id)
        .then(() => {
          this.$noty.success('Категория успешно удалена')
          this.$parent.$emit('close')
        })
        .catch((e) => {
          this.$noty.error('Oops, something went wrong!')
        })
    },

    close () {
      this.$parent.$emit('close')
    }
  }
}
</script>
