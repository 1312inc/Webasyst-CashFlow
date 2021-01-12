<template>
  <div v-if="data.length" class="custom-mb-24">
    <div class="flexbox middle custom-mb-24">
      <div class="wide flexbox middle">
        <h3 class="custom-mb-0">
          {{ dates }}
        </h3>
        <button @click="openModal = true" class="small nobutton">
          {{ $t("setDates") }}
        </button>
      </div>
      <div>
        <button @click="close" class="small nobutton">
          {{ $t("close") }}
        </button>
      </div>
    </div>

    <div v-for="currency in data" :key="currency.currency" class="flexbox">
      <div v-if="currency.income.data.length" class="tw-w-2/5">
        <div class="">
          <div class="smaller tw-uppercase">{{ $t("income") }}</div>
          <div class="larger tw-text-green-500">
            {{
              $helper.toCurrency(currency.income.totalAmount, currency.currency)
            }}
          </div>
        </div>
        <ChartPie :data="currency.income.data" />
      </div>

      <div v-if="currency.expense.data.length" class="tw-w-2/5">
        <div class="">
          <div class="smaller tw-uppercase">{{ $t("expense") }}</div>
          <div class="larger tw-text-red-500">
            {{
              $helper.toCurrency(
                currency.expense.totalAmount,
                currency.currency
              )
            }}
          </div>
        </div>
        <ChartPie :data="currency.expense.data" />
      </div>

      <div v-if="currency.income.data.length && currency.expense.data.length">
        <div class="smaller tw-uppercase">{{ $t("balance") }}</div>
        <div class="larger">
          {{
            $helper.toCurrency(
              currency.income.totalAmount - currency.expense.totalAmount,
              currency.currency
            )
          }}
        </div>
      </div>
    </div>

    <Modal v-if="openModal">
      <h2 class="custom-mb-32">
        {{ $t("setDates") }}
      </h2>
      <div class="fields custom-mb-32">
        <div class="field">
          <div class="name for-input">
            {{ $t("from") }}
          </div>
          <div class="value">
            <div class="state-with-inner-icon left">
              <DateField
                v-model="from"
                :minDate="queryParams.from"
                :maxDate="queryParams.to"
              />
              <span class="icon"><i class="fas fa-calendar"></i></span>
            </div>
          </div>
        </div>
        <div class="field">
          <div class="name for-input">
            {{ $t("to") }}
          </div>
          <div class="value">
            <div class="state-with-inner-icon left">
              <DateField
                v-model="to"
                :minDate="queryParams.from"
                :maxDate="queryParams.to"
              />
              <span class="icon"><i class="fas fa-calendar"></i></span>
            </div>
          </div>
        </div>
      </div>
      <div class="flexbox">
        <div class="flexbox space-12 wide">
          <button @click="submitModal" class="button purple">
            {{ $t("setDates") }}
          </button>
          <button @click="openModal = false" class="button light-gray">
            {{ $t("cancel") }}
          </button>
        </div>
      </div>
    </Modal>
  </div>
</template>

<script>
import api from '@/plugins/api'
import { mapState } from 'vuex'
import Modal from '@/components/Modal'
import ChartPie from '@/components/AmChartPie'
import DateField from '@/components/DateField'
import utils from '@/mixins/utilsMixin.js'

export default {
  mixins: [utils],

  components: {
    Modal,
    ChartPie,
    DateField
  },

  data () {
    return {
      data: [],
      openModal: false,
      from: '',
      to: ''
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
    this.unsubscribe = this.$store.subscribe(async mutation => {
      if (mutation.type === 'transaction/setDetailsInterval') {
        this.from = mutation.payload.from
        this.to = mutation.payload.to
        if (mutation.payload.from) {
          try {
            const { data } = await api.get('cash.aggregate.getBreakDown', {
              params: {
                from: mutation.payload.from,
                to: mutation.payload.to,
                filter: this.queryParams.filter
              }
            })
            this.data = data
          } catch (e) {
            this.handleApiError(e)
          }
        } else {
          this.data = []
        }
      }
    })
  },

  beforeDestroy () {
    this.unsubscribe()
  },

  methods: {
    close () {
      this.$store.commit('transaction/setDetailsInterval', {
        from: '',
        to: '',
        initiator: 'DetailsDashboard'
      })
    },

    submitModal () {
      this.$store.commit('transaction/setDetailsInterval', {
        from: this.from,
        to: this.to,
        initiator: 'DetailsDashboard'
      })
      this.openModal = false
    }
  }
}
</script>
