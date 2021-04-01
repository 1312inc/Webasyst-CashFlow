<template>
  <div class="dialog-body">
    <div class="dialog-header">
      <h2>{{ $t("moveTransactions", { count: count }) }}</h2>
    </div>
    <div class="dialog-content">
      <div class="fields">
        <div class="field">
          <div class="name for-input">
            {{ $t("account") }}
          </div>
          <div class="value">
            <div class="wa-select">
              <select v-model="model.account_id">
                <option value="0">{{ $t("dontChange") }}</option>
                <option
                  :value="account.id"
                  v-for="account in accounts"
                  :key="account.id"
                >
                  {{ account.currency }} – {{ account.name }} ({{
                    $helper.currencySignByCode(account.currency)
                  }})
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="field">
          <div class="name for-input">
            {{ $t("category") }}
          </div>
          <div class="value">
            <div class="wa-select">
              <select v-model="model.category_id">
                <option :value="0">{{ $t("dontChange") }}</option>
                <optgroup :label="$t('income')">
                  <option
                    :value="category.id"
                    v-for="category in categoriesIncome"
                    :key="category.id"
                  >
                    {{ category.name }}
                  </option>
                </optgroup>
                <optgroup :label="$t('expense')">
                  <option
                    :value="category.id"
                    v-for="category in categoriesExpense"
                    :key="category.id"
                  >
                    {{ category.name }}
                  </option>
                </optgroup>
              </select>
            </div>
          </div>
        </div>
        <div class="field">
          <div class="name"></div>
          <div class="value">
            <p class="hint">
              <i
                class="fas fa-exclamation-triangle"
                style="color: orangered"
              ></i>
              {{ $t("bulkMoveWarning") }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="dialog-footer">
      <div class="flexbox">
        <div class="flexbox space-12 wide">
          <button
            @click="submit"
            :disabled="controlsDisabled"
            class="button purple"
          >
            {{ $t("updateTransactions", { count: count }) }}
          </button>
          <button @click="close" class="button light-gray">
            {{ $t("cancel") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
  data () {
    return {
      model: {
        account_id: 0,
        category_id: 0
      },
      controlsDisabled: false
    }
  },

  computed: {
    ...mapState('account', ['accounts']),
    ...mapState('category', ['categories']),

    ids () {
      return this.$store.state.transactionBulk.selectedTransactionsIds
    },

    categoriesIncome () {
      return this.categories.filter(c => c.type === 'income')
    },

    categoriesExpense () {
      return this.categories.filter(c => c.type === 'expense')
    },

    count () {
      return this.ids.length
    }
  },

  methods: {
    submit () {
      this.controlsDisabled = true
      this.$store
        .dispatch('transactionBulk/bulkMove', { ids: this.ids, ...this.model })
        .then(() => {
          this.close()
        })
        .finally(() => {
          this.controlsDisabled = false
        })
    },

    close () {
      this.$parent.$emit('close')
    }
  }
}
</script>
