<template>
  <div class="dialog-body" style="overflow: initial;">
    <div class="dialog-header">
      <h2>{{ $t("moveTransactions", { count: ids.length }) }}</h2>
    </div>
    <div class="dialog-content" style="overflow: initial;">
      <div class="fields">
        <div class="field">
          <div class="name for-input">
            {{ $t("account") }}
          </div>
          <div class="value">
            <DropdownWa
              v-model="model.account_id"
              :items="[{ id: 0, name: $t('dontChange') }, ...accounts]"
              :useDefaultValue="false"
              valuePropName="id"
              :rowModificator="$_rowModificatorMixin_rowModificator_account"
              :maxHeight="200"
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
              :groupsLabels="['', $t('income'), $t('expense')]"
              :useDefaultValue="false"
              valuePropName="id"
              :rowModificator="$_rowModificatorMixin_rowModificator_category"
              :maxHeight="200"
              class="width-100"
            />
          </div>
        </div>

        <!-- Start Contractor section -->
        <div class="field">
          <div class="name for-input">{{ $t("contractor") }}</div>
          <div class="value">
            <InputContractor
              @changeContractor="
                id => {
                  model.contractor_contact_id = id;
                }
              "
              :createNewContractor="false"
            />
          </div>
        </div>
        <!-- End Contractor section -->

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
            {{ $t("updateTransactions", { count: ids.length }) }}
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
import InputContractor from '@/components/Inputs/InputContractor'
import DropdownWa from '@/components/Inputs/DropdownWa'
import rowModificatorMixin from '@/mixins/rowModificatorMixin.js'
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
      controlsDisabled: false
    }
  },

  computed: {
    ...mapState('account', ['accounts']),
    ids () {
      return this.$store.state.transactionBulk.selectedTransactionsIds
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
