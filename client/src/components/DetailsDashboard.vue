<template>
  <div v-if="data" class="custom-mb-24 c-breakdown-details">
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
        <button @click="closeDashboard" class="small nobutton larger gray">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <div v-for="currency in data" :key="currency.currency" class="flexbox">
      <div class="width-40">
        <div class="">
          <div class="smaller uppercase">{{ $t("income") }}</div>
          <div class="larger text-green">
            {{ currency.income.totalAmount > 0 ? '+' : '' }}{{
              $helper.toCurrency(currency.income.totalAmount, currency.currency)
            }}
          </div>
        </div>
        <ChartPie :data="currency.income.data" :currency="currency.currency" />
      </div>

      <div class="width-40">
        <div class="">
          <div class="smaller uppercase">{{ $t("expense") }}</div>
          <div class="larger text-red">
            {{ currency.expense.totalAmount > 0 ? '-' : '' }}{{
              $helper.toCurrency(
                currency.expense.totalAmount,
                currency.currency
              )
            }}
          </div>
        </div>
        <ChartPie :data="currency.expense.data" :currency="currency.currency" />
      </div>

      <div>
        <div class="smaller uppercase">{{ $t("balance") }}</div>
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
import DateField from '@/components/InputDate'
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
      data: null,
      from: '',
      to: '',
      openModal: false
    }
  },

  computed: {
    ...mapState('transaction', ['queryParams']),

    dates () {
      return this.from !== this.to
        ? `${this.$moment(this.from).format(
            'LL'
          )} – ${this.$moment(this.to).format('LL')}`
        : `${this.$moment(this.from).format('LL')}`
    }
  },

  created () {
    this.unsubscribe = this.$store.subscribe(async mutation => {
      if (mutation.type === 'transaction/setDetailsInterval') {
        this.from = mutation.payload.from
        this.to = mutation.payload.to
        if (this.from || this.to) {
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
          this.data = null
        }
      }
    })
  },

  beforeDestroy () {
    this.unsubscribe()
  },

  methods: {
    closeDashboard () {
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

<style lang="scss">
  .c-breakdown-details { border: 1.5px solid var(--alert-border-color); box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05), 0 0.5rem 0.5rem -0.5rem rgba(0, 0, 0, 0.13); border-radius: 0.25rem; padding: 1.5rem; }
</style>
