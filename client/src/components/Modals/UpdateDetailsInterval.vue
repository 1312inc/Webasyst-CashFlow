<template>
  <div>
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
              v-model="interval.from"
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
              v-model="interval.to"
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
        <button @click="submit" class="button purple">
          {{ $t("setDates") }}
        </button>
        <button @click="$emit('close')" class="button light-gray">
          {{ $t("cancel") }}
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState } from 'vuex'
import DateField from '@/components/InputDate'
export default {
  components: {
    DateField
  },

  data () {
    return {
      interval: {}
    }
  },

  computed: {
    ...mapState('transaction', ['queryParams', 'detailsInterval'])
  },

  created () {
    this.interval = this.detailsInterval
  },

  methods: {
    submit () {
      this.$store.dispatch('transaction/updateDetailsInterval', {
        from: this.interval.from,
        to: this.interval.to,
        outOfChart: true
      })
      this.$emit('close')
    }
  }
}
</script>
