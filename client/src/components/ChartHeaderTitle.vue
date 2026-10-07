<template>
  <Fragment>
    <div
      v-if="currentEntity"
      class="custom-pb-16-mobile"
    >
      <div>{{ $moment().format("LL") }}</div>
      <div class="flexbox middle space-12 wrap">
        <div
          class="flexbox space-12 middle wrap-mobile"
          style="min-width: 0;"
        >
          <div class="h2 custom-mb-0 text-ellipsis">
            {{ currentEntity.name || currentEntity.currency }}
          </div>

          <div
            v-if="currentEntity.currency"
            :class="{
              'text-green': balance > 0,
              'text-red': balance < 0
            }"
            class="h2 nowrap custom-mb-0"
          >
            {{
              $helper.toCurrency({
                value: balance,
                currencyCode: currentEntity.currency
              })
            }}
          </div>
        </div>

        <!-- TODO: make current currency as getter -->
        <chart-header-title-average :currency-code="$store.getters['transaction/activeCurrencyCode']" />

        <div v-if="currentEntity.id > 0 && $permissions.isAdmin">
          <button
            class="button nobutton circle"
            @click="update(currentEntity)"
          >
            <i class="fas fa-edit" />
          </button>
        </div>
      </div>
      <ul
        v-if="showScenarioChips"
        class="chips custom-mt-8 custom-mb-0"
      >
        <li :class="{ accented: !scenarioId }">
          <a @click.prevent="onScenarioClick(0)">{{ $t('defaultScenario') }}</a>
        </li>
        <li
          v-for="scenario in scenarios"
          :key="scenario.id"
          :class="{ accented: scenario.id === scenarioId }"
        >
          <a @click.prevent="onScenarioClick(scenario.id)">{{ scenario.name }}</a>
        </li>
      </ul>
      <p
        v-if="isShowImaginaryMessage"
        class="small custom-mt-12"
        style="max-width: 600px;"
      >
        {{ $t('chartHeaderImaginaryAccountsHint') }}
      </p>
      <portal>
        <Modal
          v-if="open"
          @close="close"
        >
          <component
            :is="currentComponentInModal"
            :edited-item="item"
          />
        </Modal>
      </portal>
    </div>
    <div v-else>
      <div class="skeleton">
        <span
          class="skeleton-line custom-m-0"
          style="height: 50px;"
        />
      </div>
    </div>
  </Fragment>
</template>

<script>
import Modal from '@/components/Modal'
import Account from '@/components/Modals/AddAccount'
import Category from '@/components/Modals/AddCategory'
import ChartHeaderTitleAverage from './ChartHeaderTitleAverage.vue'
import { scenarioContextService } from '@/services/scenarioContext'
export default {
  components: {
    Modal,
    Account,
    Category,
    ChartHeaderTitleAverage
  },

  data () {
    return {
      open: false,
      currentComponentInModal: '',
      item: null
    }
  },

  computed: {
    isPremium () {
      return this.$appState.isPremium
    },

    currentEntity () {
      if (
        this.$store.state.currentType === 'account' ||
        this.$store.state.currentType === 'category'
      ) {
        return this.$store.getters.getCurrentType
      }
      return this.$store.getters['balanceFlow/getBalanceFlowByCode'](
        this.$store.state.currentTypeId
      )
    },

    balance () {
      return (
        this.currentEntity.stat?.summary ||
        this.currentEntity.balances?.now.amount
      )
    },

    isShowImaginaryMessage () {
      const currency = this.$route.params.id
      if (!currency) return false
      return this.$route.name === 'Currency' &&
        this.$store.state.account.accounts.some(account => account.is_imaginary === 1 && account.currency === currency)
    },

    scenarios () {
      return this.$store.getters['scenario/sortedScenarios']
    },

    scenarioId () {
      return scenarioContextService.scenarioId
    },

    showScenarioChips () {
      return this.isPremium && this.$permissions.isAdmin && this.scenarios.length
    }

  },

  async mounted () {
    if (!this.isPremium || !this.$permissions.isAdmin) return
    const loaded = await this.$store.dispatch('scenario/getList')
    if (loaded === false) return
    const scenarioId = scenarioContextService.scenarioId
    if (!scenarioId) return
    const exists = this.scenarios.some(scenario => scenario.id === scenarioId)
    if (!exists) {
      scenarioContextService.scenarioId = 0
      window.location.reload()
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
    },

    onScenarioClick (id) {
      if (id === scenarioContextService.scenarioId) return
      scenarioContextService.scenarioId = id
      window.location.reload()
    }
  }
}
</script>
