<template>
  <div
    class="dialog-body"
    :style="legacyOverflow"
  >
    <div class="dialog-header">
      <h2>{{ $t("moveTransactions", { count: ids.length }) }}</h2>
    </div>
    <div
      class="dialog-content"
      :style="legacyOverflow"
    >
      <div class="fields">
        <div class="field">
          <div class="name for-input">
            {{ $t("account") }}
          </div>
          <div class="value">
            <DropdownWa
              v-model="model.account_id"
              :items="[{ id: 0, name: $t('dontChange') }, ...accounts]"
              :use-default-value="false"
              value-prop-name="id"
              :row-modificator="$_rowModificatorMixin_rowModificator_account"
              :max-height="200"
              class="width-100"
            />
          </div>
        </div>

        <div class="field">
          <div class="name for-input">
            {{ $t("category") }}
          </div>
          <div class="value">
            <DropdownWa
              v-model="model.category_id"
              :items="[
                [{ id: 0, name: $t('dontChange') }],
                $store.getters['category/getByType']('income'),
                $store.getters['category/getByType']('expense')
              ]"
              :groups-labels="['', $t('income'), $t('expense')]"
              :use-default-value="false"
              value-prop-name="id"
              :row-modificator="$_rowModificatorMixin_rowModificator_category"
              :max-height="200"
              class="width-100"
            />
          </div>
        </div>

        <!-- Start Contractor section -->
        <div class="field">
          <div class="name for-input">
            {{ $t("contractor") }}
          </div>
          <div class="value">
            <InputContractor
              :create-new-contractor="false"
              @changeContractor="
                id => {
                  model.contractor_contact_id = id;
                }
              "
            />
          </div>
        </div>
        <!-- End Contractor section -->

        <div class="field">
          <div class="name" />
          <div class="value">
            <p class="hint">
              <i
                class="fas fa-exclamation-triangle"
                style="color: orangered"
              />
              {{ $t("bulkMoveWarning") }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="dialog-footer">
      <p
        v-if="saveFailed"
        class="small text-red custom-mt-0"
        role="alert"
      >
        {{ $t('error.http') }}
      </p>
      <div class="flexbox">
        <div class="flexbox space-12 wide">
          <button
            :disabled="controlsDisabled"
            class="button purple"
            @click="submit"
          >
            {{ $t("updateTransactions", { count: ids.length }) }}
          </button>
          <button
            :disabled="controlsDisabled"
            class="button light-gray"
            @click="$emit('close')"
          >
            {{ $t("cancel") }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import InputContractor from '@/components/Inputs/InputContractor'
import DropdownWa from '@/components/Inputs/DropdownWa'
import rowModificatorMixin from '@/mixins/rowModificatorMixin.js'
import { appStateService } from '@/services/appState'
export default {
  components: {
    InputContractor,
    DropdownWa
  },

  mixins: [rowModificatorMixin],

  data () {
    return {
      model: {
        account_id: 0,
        category_id: 0,
        contractor_contact_id: null
      },
      controlsDisabled: false,
      saveFailed: false
    }
  },

  computed: {
    ...mapState('account', ['accounts']),
    legacyOverflow () {
      return appStateService.nativeViewport ? null : { overflow: 'initial' }
    },
    ids () {
      return this.$store.state.transactionBulk.selectedTransactionsIds
    }
  },

  methods: {
    submit () {
      if (this.controlsDisabled) return
      this.controlsDisabled = true
      this.saveFailed = false
      this.$store
        .dispatch('transactionBulk/bulkMove', { ids: this.ids, ...this.model })
        .then(result => {
          this.saveFailed = result === false
          if (!this.saveFailed) this.$emit('close')
        })
        .finally(() => {
          this.controlsDisabled = false
        })
    }
  }
}
</script>
