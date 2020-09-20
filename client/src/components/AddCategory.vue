<template>
  <div>
    <div class="mb-6 text-xl">
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

    <button class="button" @click="close">
      Отменить
    </button>
    <button class="button" @click="submit">
      {{ isModeUpdate ? "Изменить" : "Добавить" }}
    </button>
  </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import { required } from 'vuelidate/lib/validators'
export default {
  props: {
    id: {
      type: Number,
      default () {
        return null
      }
    }
  },

  data () {
    return {
      model: {
        id: null,
        name: '',
        type: '',
        color: '',
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
    ...mapGetters('category', ['getCategoryById']),

    categoryToEdit () {
      return this.getCategoryById(this.id)
    },

    isModeUpdate () {
      return this.categoryToEdit
    }
  },

  created () {
    if (this.categoryToEdit) {
      const { id, name, type, color, description } = this.categoryToEdit
      this.model = { id, name, type, color, description }
    }
  },

  methods: {
    ...mapActions('category', ['update']),

    submit () {
      this.$v.$touch()
      if (!this.$v.$invalid) {
        this.update(this.model)
          .then(() => {
            this.$noty.success('Success!')
            this.$parent.$emit('close')
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
