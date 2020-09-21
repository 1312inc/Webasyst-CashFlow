<template>
  <transition name="fade" mode="out-in">
    <div class="p-6 bg-gray-100 mb-10 rounded border">
      <div class="flex justify-between mb-6">
        <div>
          <h3 class="text-2xl">
            {{ $moment(detailsDate).format("LL") }}
          </h3>
        </div>
        <div>
          <div @click="close" class="cursor-pointer border-gray-900 border-b">
            Закрыть
          </div>
        </div>
      </div>

      <div class="flex">
        <div class="w-2/5 pr-6">
          <div class="flex items-center">
            <div class="mr-6">
              <div class="text-xl">Приход:</div>
            </div>
            <div>
              <div
                class="text-2xl border px-3 rounded bg-green-100 border-green-300 text-green-900"
              >
                {{
                  $numeral(detailsDataGenerator.income.total).format("0,0 $")
                }}
              </div>
            </div>
          </div>

          <ChartPie :data="detailsDataGenerator.income.items" />
        </div>
        <div class="w-2/5 pr-6">
          <div class="flex items-center">
            <div class="mr-6">
              <div class="text-xl">Расход:</div>
            </div>
            <div>
              <div
                class="text-2xl border px-3 rounded bg-red-100 border-red-300 text-red-900"
              >
                {{
                  $numeral(-detailsDataGenerator.expense.total).format("0,0 $")
                }}
              </div>
            </div>
          </div>
          <ChartPie :data="detailsDataGenerator.expense.items" />
        </div>
        <div class="w-1/5">
          <div class="text-xl mb-6">Баланс</div>
          <div
            class="text-2xl border px-3 rounded bg-green-100 border-green-300 inline-block"
          >
            {{ $numeral(detailsDataGenerator.balance.total).format("0,0 $") }}
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<script>
import { mapState, mapGetters, mapMutations } from 'vuex'
import ChartPie from '@/components/AmChartPie'

export default {
  components: {
    ChartPie
  },
  computed: {
    ...mapState('transaction', ['detailsDate', 'detailsDateIntervalUnit']),
    ...mapGetters('category', ['detailsDataGenerator']),
    ...mapGetters('transaction', ['getDetailsDateInterval'])
  },
  methods: {
    ...mapMutations({
      setDetailsDate: 'transaction/setDetailsDate'
    }),

    close () {
      this.setDetailsDate({ date: null })
    }
  }
}
</script>
