<template>
  <div class="dialog-body">
    <div class="dialog-header">
      <h2>
        {{ $t("setDates") }}
      </h2>
    </div>
    <div class="dialog-content">
      <div class="fields">
        <div class="field">
          <div class="name for-input">
            {{ $t("from") }}
          </div>
          <div class="value">
            <div class="state-with-inner-icon left">
              <DateField
                v-model="interval.from"
                :min-date="chartInterval.from"
                :max-date="chartInterval.to"
              />
              <span class="icon"><i class="fas fa-calendar" /></span>
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
                v-model="interval.to"
                :min-date="chartInterval.from"
                :max-date="chartInterval.to"
              />
              <span class="icon"><i class="fas fa-calendar" /></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="dialog-footer">
      <div class="flexbox">
        <div class="flexbox space-12 wide">
          <button
            :disabled="controlsDisabled"
            class="button purple"
            @click="submit"
          >
            {{ $t("setDates") }}
          </button>
          <button
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
import DateField from '@/components/Inputs/InputDate'
export default {
  components: {
    DateField
  },

  data () {
    return {
      interval: {},
      controlsDisabled: false
    }
  },

  computed: {
    ...mapState('transaction', ['chartInterval', 'detailsInterval'])
  },

  created () {
    this.interval = { ...this.detailsInterval }
  },

  methods: {
    submit () {
      this.controlsDisabled = true
      this.$store
        .dispatch('transaction/updateDetailsInterval', {
          from: this.interval.from,
          to: this.interval.to,
          outOfChart: true
        })
        .then(() => {
          this.$emit('close')
        })
        .finally(() => {
          this.controlsDisabled = true
        })
    }
  }
}
</script>
