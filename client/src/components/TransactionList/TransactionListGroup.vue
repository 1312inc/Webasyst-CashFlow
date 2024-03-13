<template>
  <div class="c-transaction-section">
    <div
      ref="el"
      @mouseover="
        isHover = true;
        if ($refs.pieIcon) $refs.pieIcon.style.display = 'block';
      "
      @mouseleave="
        isHover = false;
        if ($refs.pieIcon) $refs.pieIcon.style.display = 'none';
      "
    >
      <div
        class="c-sticky-header-group"
        :class="{'c-sticky-header-group--offset': $store.state.transactionBulk.selectedTransactionsIds.length}"
      >
        <div class="flexbox middle space-12 wrap-mobile justify-between custom-px-8 custom-py-12">
          <div class="flexbox middle space-12">
            <div
              v-if="$helper.showMultiSelect()"
              :class="{ 'desktop-only': $helper.isDesktopEnv }"
              style="min-width: 1rem"
            >
              <span
                v-show="isHoverComputed && filteredTransactions.length"
                class="wa-checkbox"
                @click="checkAll(filteredTransactions)"
              >
                <input
                  type="checkbox"
                  :checked="isCheckedAllInGroup(filteredTransactions)"
                >
                <span>
                  <span class="icon">
                    <i class="fas fa-check" />
                  </span>
                </span>
              </span>
            </div>

            <h4
              v-if="!showFoundedCount"
              class="c-transaction-section__header nowrap"
            >
              <div
                v-if="type === 'overdue'"
                class="black"
              >
                {{ $t("overdue") }}
              </div>
              <div
                v-if="type === 'yesterday'"
                class="black"
              >
                {{ $t("yesterday") }}
              </div>
              <div
                v-if="type === 'tomorrow'"
                class="black"
              >
                {{ $t("tomorrow") }}
              </div>
              <div
                v-if="type === 'today'"
                class="black"
              >
                {{ $t("today") }}
                <span class="hint">
                  {{ $moment.locale() === 'ru' ? $moment().format("D MMMM") : $moment().format("MMMM D") }}
                </span>
              </div>
              <div
                v-if="type === 'future'"
                :class="{ 'opacity-50': !upcomingBlockOpened }"
                class="black flexbox middle space-8"
                style="cursor: pointer"
                @click="upcomingBlockOpened = !upcomingBlockOpened"
              >
                <span>{{
                  $t("nextDays", { count: featurePeriod })
                }}</span>
              </div>
              <div
                v-if="$moment(new Date(type)).isValid()"
                class="black"
                style="text-transform: capitalize"
              >
                {{ $moment(type).format("MMMM YYYY") }}
              </div>
            </h4>

            <div
              v-if="showFoundedCount"
              class="gray bold nowrap custom-mr-4"
            >
              {{ $t('found', { count: filteredTransactions.length }) }}
            </div>

            <TransactionListGroupUpcomingPeriod
              v-if="type === 'future'"
              :upcoming-block-opened="upcomingBlockOpened"
              @updateUpcomingBlockOpened="(val) => upcomingBlockOpened = val"
            />
          </div>
          <div class="flexbox middle space-12">
            <div
              v-if="filteredTransactions.length"
              ref="pieIcon"
              class="desktop-only c-pie-icon-helper"
              style="display: none; cursor: pointer"
              @click="onStick"
            >
              <i class="fas fa-chart-pie" />
            </div>
            <AmountForGroup
              :group="filteredTransactions"
            />
          </div>
        </div>
      </div>

      <div v-if="upcomingBlockOpened">
        <ul
          v-if="filteredTransactions.length"
          class="c-list list"
          :class="{'c-list--compact': compactModeForFutureList}"
        >
          <TransactionListGroupRow
            v-for="(transaction, i) in filteredTransactions"
            v-show="isShown(transaction)"
            :key="transaction.id"
            :transaction="transaction"
            :show-checker="isShowChecker"
            :is-compact-mode="compactModeForFutureList"
            :collapse-header-data="collapseHeaderData(transaction)"
            :show-date="i === 0 ? true : filteredTransactions[i].date !== filteredTransactions[i - 1].date"
            :visible-select-checkbox="visibleSelectCheckbox"
            @toggleCollapseHeader="handleCollapseHeaderClick(transaction)"
          />
          <li
            v-if="type === 'future' && $store.state.transaction.showFutureTransactionsMoreLink[featurePeriod]"
            class="flexbox middle"
          >
            <button
              class="button light-gray"
              style="margin: 1rem auto;"
              @click.prevent="() => $store.dispatch('transaction/fetchTransactionsFuture')"
            >
              <span
                v-show="$store.state.transaction.loadingFuture"
                class="custom-mr-8"
              >
                <i class="fas fa-spinner wa-animation-spin speed-1000" />
              </span>
              <span>{{ $t('showMore') }}</span>
            </button>
          </li>
        </ul>
        <SkeletonTransaction
          v-else-if="(type === 'future' || type === 'tomorrow') && $store.state.transaction.loadingFuture && !$store.getters['transaction/getFutureTransactions'].length"
          :lines="1"
        />
        <div
          v-else
          class="align-center small custom-py-24"
        >
          {{ $t("emptyList") }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import { useStorage, useElementBounding } from '@vueuse/core'
import TransactionListGroupUpcomingPeriod from './TransactionListGroupUpcomingPeriod'
import TransactionListGroupRow from './TransactionListGroupRow/TransactionListGroupRow'
import AmountForGroup from '@/components/PeriodAmount/AmountForGroup'
import SkeletonTransaction from './SkeletonTransaction'

const listCompactMode = useStorage('list_compact_mode', true)

export default {

  components: {
    TransactionListGroupUpcomingPeriod,
    TransactionListGroupRow,
    AmountForGroup,
    SkeletonTransaction
  },
  props: {
    group: {
      type: Array,
      required: true
    },
    type: {
      type: String
    },
    index: {
      type: Number
    },
    visibleSelectCheckbox: {
      type: Boolean,
      default: false
    },
    showFoundedCount: {
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      isHover: false,
      сollapseGroups: {},
      activeCollapseExternalSourceIDs: [],
      localStorage: (() => {
        try {
          return JSON.parse(localStorage.getItem('upcoming_transactions_show') || undefined)
        } catch (e) {
          return true
        }
      })()
    }
  },

  computed: {
    compactModeForFutureList () {
      return this.type === 'future' && listCompactMode.value
    },

    isShowChecker () {
      return this.filteredTransactions.some(e =>
        this.$store.state.transactionBulk.selectedTransactionsIds.includes(e.id)
      )
    },

    isHoverComputed () {
      if (this.$isSpaMobileMode || this.visibleSelectCheckbox) return true
      return this.isShowChecker ? true : this.isHover
    },

    featurePeriod () {
      return this.$store.state.transaction.featurePeriod
    },

    upcomingBlockOpened: {
      get () {
        if (this.type !== 'future') return true
        return this.localStorage
      },

      set (val) {
        this.localStorage = val
        localStorage.setItem(
          'upcoming_transactions_show',
          val
        )
      }
    },

    filteredTransactions () {
      let result = this.group

      if (this.type === 'future') {
        result = this.group.filter(t => {
          const start = this.$moment().add(1, 'd')
          const end = this.$moment().add(this.featurePeriod, 'd')
          return this.$moment(t.date).isBetween(start, end)
        }).reverse()
      }

      return result
    }
  },

  watch: {
    filteredTransactions: {
      handler (val) {
        if (
          this.$store.state.transaction.activeGroupTransactions.index ===
          this.index
        ) {
          this.onStick()
        }

        const hash = {}
        val
          .filter(e => e.external_source)
          .forEach(e => {
            const key = `${e.category_id}|${e.date}|${e.external_source}`
            if (!hash[key]) {
              hash[key] = {
                ids: [e.id],
                totalAmount: e.amount
              }
            } else {
              hash[key].ids.push(e.id)
              hash[key].totalAmount += e.amount
            }
          })

        // remove keys with only one transaction
        for (const k in hash) {
          if (hash[k].ids.length < 2) {
            delete hash[k]
          }
        }

        this.сollapseGroups = hash
      },
      immediate: true
    }
  },

  mounted () {
    const that = this

    const enableWatch = ref(true)
    const { top } = useElementBounding(this.$refs.el)

    watch(top, top => {
      if (top < 120 && enableWatch.value) {
        that.onStick()
        enableWatch.value = false
      }
      if (top > 120 && !enableWatch.value) {
        that.onStick()
        enableWatch.value = true
      }
    })
  },

  created () {
    if (this.index === 0) {
      this.onStick()
    }
  },

  methods: {
    isShown (transaction) {
      const key = `${transaction.category_id}|${transaction.date}|${transaction.external_source}`
      if (
        !this.сollapseGroups[key] ||
        this.сollapseGroups[key].ids[0] === transaction.id
      ) {
        return true
      }
      return this.activeCollapseExternalSourceIDs.includes(key)
    },

    collapseHeaderData (transaction) {
      const key = `${transaction.category_id}|${transaction.date}|${transaction.external_source}`
      if (
        this.isShown(transaction) &&
        !this.activeCollapseExternalSourceIDs.includes(key)
      ) {
        // TODO: need refactor
        // console.log(this.сollapseGroups[key])
        return this.сollapseGroups[key]
      } else {
        return null
      }
    },

    handleCollapseHeaderClick (transaction) {
      const key = `${transaction.category_id}|${transaction.date}|${transaction.external_source}`
      const i = this.activeCollapseExternalSourceIDs.indexOf(key)
      if (i > -1) {
        this.activeCollapseExternalSourceIDs.splice(i, 1)
      } else {
        this.activeCollapseExternalSourceIDs.push(key)
      }
    },

    checkAll (items) {
      const ids = items.map(e => e.id)
      const method = this.isCheckedAllInGroup(items) ? 'unselect' : 'select'
      this.$store.commit(`transactionBulk/${method}`, ids)
      // reset last checkbox selection
      this.$store.commit('transactionBulk/setLastCheckboxIndex', -1)
    },

    isCheckedAllInGroup (items) {
      return items.every(e =>
        this.$store.state.transactionBulk.selectedTransactionsIds.includes(e.id)
      )
    },

    onStick () {
      this.$store.commit('transaction/setActiveGroupTransactions', {
        index: this.index,
        name: this.type,
        items: this.filteredTransactions
      })
    }
  }
}
</script>

<style>
.c-sticky-header-group {
  position: sticky;
  top: calc(60px + 4rem);
  z-index: 99;
  background-color: var(--background-color-blank);
}

.no-sticky-controls .c-sticky-header-group {
  top: 4rem;
}

.c-sticky-header-group:hover {
  z-index: 999;
}

.c-list--compact button {
  margin-left: 1rem !important;
}

@media screen and (max-width: 760px) {
  .c-sticky-header-group {
    top: 4rem;
  }
  .c-mobile-build .c-sticky-header-group {
    top: 0px;
  }
  .c-mobile-build .c-sticky-header-group--offset {
    top: 60px;
  }
}

.c-pie-icon-helper {
  opacity: 0.5;
  transition: 0.2s opacity;
}
.c-pie-icon-helper:hover {
  opacity: 1;
}
</style>
