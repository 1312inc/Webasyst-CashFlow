<template>
  <div class="custom-mb-24">
    <div
      @mouseover="isHover = true"
      @mouseleave="isHover = false"
      v-if="group.length"
    >
      <div class="flexbox flexbox-mobile middle">
        <div class="flexbox middle space-12 wide">
          <div v-if="$helper.showMultiSelect()" style="width: 1rem">
            <span
              v-show="isHoverComputed"
              @click="checkAll(group)"
              class="wa-checkbox"
            >
              <input type="checkbox" :checked="isCheckedAllInGroup(group)" />
              <span>
                <span class="icon">
                  <i class="fas fa-check"></i>
                </span>
              </span>
            </span>
          </div>
          <div v-if="title">
            <div v-if="title === 'today'" class="black">
              {{ $t("today") }}
            </div>
            <div v-else-if="title === 'items'" class="black">
              {{ $t("nextDays", { count: 7 }) }}
            </div>
            <div v-else class="black">
              {{ $moment(title).format("MMMM, YYYY") }}
            </div>
          </div>
          <div class="hint">
            {{ $t("transactionsListCount", { count: group.length }) }}
          </div>
        </div>
        <div class="flexbox middle space-12">
          <div>
            <AmountForGroup :group="group" type="income" />
          </div>
          <div>
            <AmountForGroup :group="group" type="expense" />
          </div>
        </div>
      </div>

      <ul class="c-list list">
        <TransactionListRow
          v-for="transaction in group"
          :key="transaction.id"
          :transaction="transaction"
          :showChecker="isShowChecker"
        />
      </ul>
    </div>
    <div v-else>
      <div class="flexbox space-12">
        <div v-if="$helper.showMultiSelect()" style="width: 1rem"></div>
        <div v-if="title === 'today'" class="black">
          {{ $t("today") }}
        </div>
        <div v-else-if="title === 'items'" class="black">
          {{ $t("nextDays", { count: 7 }) }}
        </div>
        <div v-else class="black">
          {{ $moment(title).format("MMMM, YYYY") }}
        </div>
      </div>
      <div class="tw-text-center custom-py-20">
        {{ $t("emptyListToday") }}
      </div>
    </div>
  </div>
</template>

<script>
import TransactionListRow from '@/components/TransactionListRow'
import AmountForGroup from '@/components/AmountForGroup'
export default {
  props: {
    group: {
      type: Array,
      required: true
    },

    title: {
      type: String
    }
  },

  components: {
    TransactionListRow,
    AmountForGroup
  },

  data () {
    return {
      isHover: false
    }
  },

  computed: {
    isShowChecker () {
      return this.group.some(e =>
        this.$store.state.transactionBulk.selectedTransactionsIds.includes(e.id)
      )
    },

    isHoverComputed () {
      if (process.env.VUE_APP_MODE === 'mobile') return true
      return this.isShowChecker ? true : this.isHover
    }
  },

  methods: {
    checkAll (items) {
      const ids = items.map(e => e.id)
      const method = this.isCheckedAllInGroup(items) ? 'unselect' : 'select'
      this.$store.commit(`transactionBulk/${method}`, ids)
    },

    isCheckedAllInGroup (items) {
      return items.every(e =>
        this.$store.state.transactionBulk.selectedTransactionsIds.includes(e.id)
      )
    }
  }
}
</script>

<style>
@media screen and (max-width: 760px) {
  .flexbox-mobile {
    flex-direction: column;
  }
  .flexbox-mobile.middle,
  .flexbox-mobile > *.middle {
    align-self: baseline;
  }
}
</style>
