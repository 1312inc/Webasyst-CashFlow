<template>
  <div class="dialog-body">
    <div class="dialog-header">
      <h2>
        {{ isModeUpdate ? $t("updateAccount") : $t("addAccount") }}
      </h2>
    </div>

    <div class="dialog-content">
      <div class="fields">
        <div class="field">
          <div class="name for-input">
            {{ $t("name") }}
          </div>
          <div class="value">
            <input
              v-model="model.name"
              @keyup.enter="submit('account')"
              :class="{ 'state-error': $v.model.name.$error }"
              ref="focus"
              class="bold large"
              type="text"
            />
          </div>
        </div>

        <div class="field">
          <div class="name for-input">
            {{ $t("currency") }}
          </div>
          <div class="value">
            <div class="wa-select solid">
              <select
                v-model="model.currency"
                class="width-50"
                :class="{ 'state-error': $v.model.currency.$error }"
              >
                <option
                  :value="c.code"
                  v-for="c in sortedCurrencies"
                  :key="c.code"
                >
                  {{ c.code }} – {{ c.title }} ({{ c.sign }})
                </option>
              </select>
            </div>
          </div>
        </div>

        <div v-if="!isModeUpdate" class="field">
          <div class="name for-input">
            {{ $t("startingBalance") }}
          </div>
          <div class="value">
            <input-currency
              v-model="model.starting_balance"
              :currencyCode="model.currency"
              :showSign="false"
              :short="true"
              placeholder="0"
            />
          </div>
        </div>

        <div class="field">
          <div class="name for-input">
            {{ $t("icon") }}
          </div>
          <div class="value">
            <div class="с-icon-uploader">
              <div
                v-if="$helper.isValidHttpUrl(model.icon)"
                class="flexbox middle"
              >
                <div
                  class="с-icon-uploader-image"
                  :style="`background-image:url('${model.icon}')`"
                ></div>
                <div>
                  <a
                    href="#"
                    @click.prevent="model.icon = ''"
                    class="button nobutton smaller"
                    >{{ $t("delete") }}</a
                  >
                </div>
              </div>
              <div v-else>
                <IconUploader @uploaded="setIcon" />
              </div>
            </div>
          </div>
        </div>

        <div class="field">
          <div class="name for-input">
            {{ $t("desc") }}
          </div>
          <div class="value">
            <textarea
              v-model="model.description"
              class="width-100"
              rows="4"
              style="resize: none; height: auto"
              :placeholder="$t('optional')"
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <div class="dialog-footer">
      <div class="flexbox">
        <div class="flexbox space-12 wide">
          <button
            @click="submit('account')"
            :disabled="controlsDisabled"
            class="button purple"
          >
            {{ isModeUpdate ? $t("update") : $t("add") }}
          </button>
          <button @click="close" class="button light-gray">
            {{ $t("cancel") }}
          </button>
        </div>
        <button
          v-if="isModeUpdate"
          @click="remove('account')"
          :disabled="controlsDisabled"
          class="button outlined red"
        >
          <span>{{ $t("delete") }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { required } from 'vuelidate/lib/validators'
import updateEntityMixin from '@/mixins/updateEntityMixin'
import InputCurrency from '@/components/Inputs/InputCurrency'
import IconUploader from '@/components/Inputs/IconUploader'
export default {
  mixins: [updateEntityMixin],

  props: ['editedItem'],

  components: {
    InputCurrency,
    IconUploader
  },

  data () {
    return {
      model: {
        id: null,
        name: '',
        currency: '',
        starting_balance: '',
        icon: '',
        description: ''
      }
    }
  },

  validations: {
    model: {
      name: {
        required
      },
      currency: {
        required
      }
    }
  },

  computed: {
    sortedCurrencies () {
      return [...this.$store.state.system.currencies].sort((a, b) => {
        if (a.code < b.code) { return -1 }
        if (a.code > b.code) { return 1 }
        return 0
      })
    }
  },

  mounted () {
    this.$refs.focus.focus()
  },

  methods: {
    setIcon (url) {
      this.model.icon = url
    }
  }
}
</script>

<style lang="scss">
.с-icon-uploader {
  &-image {
    width: 44px;
    height: 44px;
    border-radius: 0.25rem;
    background: {
      position: center center;
      size: cover;
    }
  }
}
</style>
