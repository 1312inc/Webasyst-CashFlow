<template>
  <transition name="fade" appear>
    <div>
        <div class="flexbox">
          <div class="wide">
            <h3>
              {{ dates }}
            </h3>
          </div>
          <div>
            <button @click="close" class="smaller">
              Закрыть
            </button>
          </div>
        </div>

        <div class="flexbox fixed">
          <div>
            <div class="flexbox middle">
              <div>Приход:</div>
              <div>
                {{ $numeral(detailsDataGenerator.income.total).format() }}
              </div>
            </div>
            <ChartPie :data="detailsDataGenerator.income.items" />
          </div>

          <div>
            <div class="flexbox middle">
              <div>Расход:</div>
              <div>
                {{ $numeral(-detailsDataGenerator.expense.total).format() }}
              </div>
            </div>
            <ChartPie :data="detailsDataGenerator.expense.items" />
          </div>

          <div>
            <div class="text-xl mb-6">Баланс</div>
            <div>
              {{ $numeral(detailsDataGenerator.balance.total).format() }}
            </div>
          </div>
        </div>
    </div>
  </transition>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import ChartPie from '@/components/AmChartPie'

export default {
  components: {
    ChartPie
  },
  computed: {
    ...mapState('transaction', ['interval', 'detailsInterval']),
    ...mapGetters('category', ['detailsDataGenerator']),

    dates () {
      return this.detailsInterval.from !== this.detailsInterval.to
        ? `${this.$moment(this.detailsInterval.from).format(
            'LL'
          )} – ${this.$moment(this.detailsInterval.to).format('LL')}`
        : `${this.$moment(this.detailsInterval.from).format('LL')}`
    }
  },
  methods: {
    close () {
      this.$store.dispatch('transaction/setdetailsInterval', {
        from: '',
        to: ''
      })
    }
  }
}
</script>
