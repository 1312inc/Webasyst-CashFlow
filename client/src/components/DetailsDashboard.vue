<template>
  <!-- <transition name="fade" appear> -->
    <div v-if="data.length" class="custom-mb-24">
        <div class="flexbox middle custom-mb-24">
          <div class="wide">
            <h3>
              {{ dates }}
            </h3>
          </div>
          <div>
            <button @click="close" class="small nobutton">
              Закрыть
            </button>
          </div>
        </div>

        <div v-for="currency in data" :key="currency.currency" class="flexbox fixed">
          <div v-if="currency.income.data.length">
            <div class="flexbox middle">
              <div class="custom-mr-8">{{ $t('income') }}:</div>
              <div class="larger">
                {{ $numeral(currency.income.totalAmount).format() }}
              </div>
            </div>
            <ChartPie :data="currency.income.data" />
          </div>

          <div v-if="currency.expense.data.length">
            <div class="flexbox middle">
              <div class="custom-mr-8">{{ $t('expense') }}:</div>
              <div class="larger">
                {{ $numeral(currency.expense.totalAmount).format() }}
              </div>
            </div>
            <ChartPie :data="currency.expense.data" />
          </div>

          <div v-if="currency.income.data.length && currency.expense.data.length">
            <div class="text-xl mb-6">{{ $t('balance') }}</div>
            <div class="larger">
              {{ $numeral(currency.income.totalAmount - currency.expense.totalAmount).format() }}
            </div>
          </div>
        </div>
    </div>
  <!-- </transition> -->
</template>

<script>
import api from '@/plugins/api'
import { mapState } from 'vuex'
import ChartPie from '@/components/AmChartPie'

export default {
  components: {
    ChartPie
  },

  data () {
    return {
      data: []
    }
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval']),

    dates () {
      return this.detailsInterval.from !== this.detailsInterval.to
        ? `${this.$moment(this.detailsInterval.from).format(
            'LL'
          )} – ${this.$moment(this.detailsInterval.to).format('LL')}`
        : `${this.$moment(this.detailsInterval.from).format('LL')}`
    }
  },

  created () {
    this.unsubscribe = this.$store.subscribe(async (mutation) => {
      if (mutation.type === 'transaction/setDetailsInterval') {
        if (this.detailsInterval.from) {
          this.$store.commit('transaction/updateQueryParams', { offset: 0 })
          const { data } = await api.get('cash.aggregate.getBreakDown', {
            params: {
              from: this.detailsInterval.from,
              to: this.detailsInterval.to,
              filter: this.queryParams.filter
            }
          })
          this.data = data
          this.subscribeToQueryParams()
        } else {
          this.data = []
          if (this.unsubscribeQuery) {
            this.unsubscribeQuery()
          }
        }
      }
    })
  },

  beforeDestroy () {
    this.unsubscribe()
    if (this.unsubscribeQuery) {
      this.unsubscribeQuery()
    }
  },

  methods: {
    subscribeToQueryParams () {
      this.unsubscribeQuery = this.$store.subscribe((mutation) => {
        if (mutation.type === 'transaction/updateQueryParams') {
          const keys = Object.keys(mutation.payload)
          const key = keys[0]
          const changeOffset = keys.length === 1 && key === 'offset'

          if (!changeOffset) {
            this.$store.commit('transaction/setDetailsInterval', { from: '', to: '' })
          }
        }
      })
    },

    close () {
      this.$store.commit('transaction/setDetailsInterval', { from: '', to: '' })
      this.$store.commit('transaction/updateQueryParams', { offset: 0 })
    }
  }
}
</script>
