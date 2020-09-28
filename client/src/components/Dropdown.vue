<template>
  <div class="space-y-1 relative z-50" v-click-outside="onClickOutside">
    <label v-if="title" class="text-sm leading-5 font-medium text-gray-700">
      {{ title }}
    </label>
    <div class="relative">
      <span
        @click="show = true"
        class="inline-block w-full rounded-md shadow-sm"
      >
        <button
          type="button"
          aria-haspopup="listbox"
          aria-expanded="true"
          aria-labelledby="listbox-label"
          class="cursor-default relative w-full rounded-md border border-gray-300 bg-white pl-3 pr-10 text-left focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition ease-in-out duration-150 sm:text-sm sm:leading-5"
        >
          <div class="flex items-center space-x-3">
            <span class="block truncate">
              {{ items[selectedIndex].title }}
            </span>
          </div>
          <span
            class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none"
          >
            <svg
              class="h-5 w-5 text-gray-400"
              viewBox="0 0 20 20"
              fill="none"
              stroke="currentColor"
            >
              <path
                d="M7 7l3-3 3 3m0 6l-3 3-3-3"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </span>
        </button>
      </span>

      <transition name="fade" :duration="100">
        <div
          v-if="show"
          class="absolute mt-1 w-full rounded-md bg-white shadow-lg"
        >
          <ul
            tabindex="-1"
            role="listbox"
            aria-labelledby="listbox-label"
            aria-activedescendant="listbox-item-3"
            class="max-h-56 rounded-md py-1 text-base leading-6 shadow-xs overflow-auto focus:outline-none sm:text-sm sm:leading-5"
          >

            <li
              v-for="(item, i) in items"
              :key="i"
              @click="selectItem(i)"
              role="option"
              class="text-gray-900 cursor-default select-none relative pl-3 pr-9 hover:bg-gray-200"
            >
              <div class="flex items-center space-x-3">
                <!-- Selected: "font-semibold", Not Selected: "font-normal" -->
                <span class="font-normal block truncate">
                  {{ item.title }}
                </span>
              </div>

              <span
                v-if="selectedIndex === i"
                class="absolute inset-y-0 right-0 flex items-center pr-4"
              >
                <!-- Heroicon name: check -->
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path
                    fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd"
                  />
                </svg>
              </span>
            </li>
          </ul>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    items: {
      type: Array
    },

    defaultSelectedIndex: {
      type: Number,
      default () {
        return 0
      }
    },

    title: {
      type: String
    }
  },

  data () {
    return {
      show: false,
      selectedIndex: null
    }
  },

  created () {
    this.selectedIndex = this.defaultSelectedIndex
  },

  methods: {
    selectItem (index) {
      this.selectedIndex = index
      this.show = false
      this.$emit('selected', this.selectedIndex, this.items[this.selectedIndex].value)
    },

    onClickOutside () {
      this.show = false
    }
  }
}
</script>
