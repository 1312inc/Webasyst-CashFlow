<template>
  <div>
    <h2 class="custom-mb-32">{{ $t("moveTransactions", { count: count }) }}</h2>

    <div class="fields custom-mb-32">
      <div class="field">
        <div class="name for-input">
          {{ $t("account") }}
        </div>
        <div class="value">
          <div class="wa-select">
            <select
              v-model="model.account_id"
            >
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
            <select
              v-model="model.category_id"
            >
              <option :value=0>{{ $t("dontChange") }}</option>
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
    </div>

    <span class="smaller alert warning custom-mb-24">
      <i class="fas fa-exclamation-triangle"></i>
      {{ $t("bulkMoveWarning") }}
    </span>

    <div class="flexbox">
      <div class="flexbox space-1rem wide">
        <button @click="submit" class="button purple">
          {{ $t("updateTransactions", { count: count }) }}
        </button>
        <button @click="close" class="button light-gray">
          {{ $t("cancel") }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
export default {
  props: {
    ids: {
      type: Array,
      required: true
    }
  },

  data () {
    return {
      model: {
        ids: this.ids,
        account_id: 0,
        category_id: 0
      }
    }
  },

  computed: {
    ...mapState('account', ['accounts']),
    ...mapState('category', ['categories']),

    categoriesIncome () {
      return this.categories.filter((c) => c.type === 'income')
    },

    categoriesExpense () {
      return this.categories.filter((c) => c.type === 'expense')
    },

    count () {
      return this.ids.length
    }
  },

  methods: {
    submit () {
      this.$store
        .dispatch('transaction/bulkMove', this.model)
        .then(() => {
          this.$emit('success')
          this.close()
        })
        .catch(() => {})
    },

    close () {
      this.$parent.$emit('close')
    }
  }
}
</script>
