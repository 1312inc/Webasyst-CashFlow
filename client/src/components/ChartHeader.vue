<template>
  <div>
    <div class="flexbox custom-mb-12">
      <div v-if="$helper.isDesktopEnv" class="wide">
        <div v-if="currentCategory && !currentCategory.name">
          <div class="h2 custom-mb-0">{{ $t("cashToday") }}</div>
        </div>
        <div v-if="currentCategory && currentCategory.name">
          <div class="flexbox middle space-1rem">
            <div class="h2 custom-mb-0">{{ currentCategory.name }}</div>
            <div
              v-if="currentCategory.stat && currentCategory.stat.summary"
              class="larger"
            >
              {{ $numeral(currentCategory.stat.summary).format() }}
              {{ getCurrencySignByCode(currentCategory.currency) }}
            </div>
            <div v-if="currentCategory.id >= 0">
              <button
                @click="update(currentCategory)"
                class="button nobutton smaller"
              >
                <i class="fas fa-sliders-h"></i> {{ $t("changeSettings") }}
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="flexbox space-1rem">
        <div>
          <Dropdown type="from" />
        </div>
        <div>
          <Dropdown type="to" />
        </div>
      </div>
    </div>

    <Modal v-if="open" @close="close">
      <component :is="currentComponentInModal" :editedItem="item"></component>
    </Modal>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import Modal from '@/components/Modal'
import Account from '@/components/AddAccount'
import Category from '@/components/AddCategory'
import Dropdown from '@/components/Dropdown'

export default {
  components: {
    Modal,
    Account,
    Category,
    Dropdown
  },

  data () {
    return {
      open: false,
      currentComponentInModal: '',
      item: null
    }
  },

  computed: {
    ...mapGetters('system', ['getCurrencySignByCode']),

    currentCategory () {
      return this.$store.getters.getCurrentType
    }
  },

  methods: {
    update (item) {
      this.open = true
      this.currentComponentInModal = item.currency ? 'Account' : 'Category'
      this.item = item
    },

    close () {
      this.open = false
      this.currentComponentInModal = ''
    }
  }
}
</script>
