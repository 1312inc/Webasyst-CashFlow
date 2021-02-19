<template>
  <div v-if="data" class="c-breakdown-details">
    <div class="flexbox middle custom-mb-24 wrap-mobile vertical-mobile">
      <div class="wide flexbox middle">
        <h3 class="custom-mb-0">
          {{ dates }}
        </h3>
        <button @click="openModal = true" class="button light-gray custom-ml-12">
          {{ $t("setDates") }}
        </button>
      </div>
      <div>
        <button @click="closeDashboard" class="nobutton largest">
          <i class="fas fa-times gray"></i>
        </button>
      </div>
    </div>

    <div v-for="currency in data" :key="currency.currency" class="flexbox">
      <div class="width-40 width-100-mobile">
        <div class="">
          <div class="align-center uppercase small gray bold">{{ $t("income") }}</div>
          <div class="align-center largest text-green bold custom-mt-4">
            {{
              $helper.toCurrency({
                value: currency.income.totalAmount,
                currencyCode: currency.currency,
                isDynamics: true,
              })
            }}
          </div>
        </div>
        <ChartPie :data="currency.income.data" :currency="currency.currency" />
        <AmChartLegend
          :legendItems="currency.income.data"
          :currencyCode="currency.currency"
        />
      </div>

      <div class="width-40 width-100-mobile">
        <div class="">
          <div class="align-center uppercase small gray bold">{{ $t("expense") }}</div>
          <div class="align-center largest text-red bold custom-mt-4">
            {{
              $helper.toCurrency({
                value: currency.expense.totalAmount,
                currencyCode: currency.currency,
                isDynamics: true,
                isReverse: true,
              })
            }}
          </div>
        </div>
        <ChartPie :data="currency.expense.data" :currency="currency.currency" />
        <AmChartLegend
          :legendItems="currency.expense.data"
          :currencyCode="currency.currency"
          :isReverse="true"
        />
      </div>

      <div class="width-20 width-100-mobile">
        <div class="align-center uppercase small gray bold">{{ $t("balance") }}</div>
        <div class="align-center largest bold black custom-mt-4">
          {{
            $helper.toCurrency({
              value: currency.income.totalAmount - currency.expense.totalAmount,
              currencyCode: currency.currency,
            })
          }}
        </div>
      </div>
    </div>

    <portal>
      <Modal v-if="openModal">
        <UpdateDetailsInterval @close="openModal = false" />
      </Modal>
    </portal>
  </div>
</template>

<script>
import api from '@/plugins/api'
import { mapState } from 'vuex'
import Modal from '@/components/Modal'
import ChartPie from '@/components/AmChartPie'
import AmChartLegend from '@/components/AmChartLegend'
import UpdateDetailsInterval from '@/components/Modals/UpdateDetailsInterval'

export default {
  components: {
    Modal,
    ChartPie,
    AmChartLegend,
    UpdateDetailsInterval
  },

  data () {
    return {
      data: null,
      openModal: false
    }
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval']),

    dates () {
      return this.detailsInterval.from !== this.detailsInterval.to
        ? `${this.$moment(this.detailsInterval.from).format('LL')} – ${this.$moment(
            this.detailsInterval.to
          ).format('LL')}`
        : `${this.$moment(this.detailsInterval.from).format('LL')}`
    }
  },

  watch: {
    detailsInterval: 'fetchBreakDown'
  },

  methods: {
    fetchBreakDown (val) {
      if (val.from && val.to) {
        api.get('cash.aggregate.getBreakDown', {
          params: {
            from: val.from,
            to: val.to,
            filter: this.queryParams.filter
          }
        })
          .then(({ data }) => {
            this.data = data
          })
      } else {
        this.data = null
      }
    },

    closeDashboard () {
      this.$store.dispatch('transaction/updateDetailsInterval', {
        from: '',
        to: ''
      })
    }
  }
}
</script>

<style lang="scss">
.c-breakdown-details {
  border: 3px solid var(--alert-border-color);
  box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.1),
    0 0.5rem 1.5rem -0.5rem rgba(0, 0, 0, 0.2);
  border-radius: 0.375rem;
  padding: 1.5rem 2rem;
  margin: 1.7rem;
}
</style>
