<template>
  <div class="dialog-body">
    <div class="dialog-header">
      <div class="flexbox middle full-width custom-mb-12">
        <h2 class="custom-m-0">
          {{ isModeUpdate ? $t("updateAccount") : $t("addAccount") }}
        </h2>
        <button
          v-if="isModeUpdate"
          :disabled="controlsDisabled"
          class="button outlined red mobile-only"
          @click="remove('account')"
        >
          <span><i class="fas fa-trash-alt" /></span>
        </button>
      </div>
    </div>

    <div class="dialog-content">
      <div class="fields">
        <div class="field">
          <div class="name for-input">
            {{ $t("name") }}
          </div>
          <div class="value">
            <input
              ref="focus"
              v-model="model.name"
              :class="{ 'state-error': $v.model.name.$error }"
              class="bold large"
              type="text"
              @keyup.enter="submit('account')"
            >
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
                  v-for="c in sortedCurrencies"
                  :key="c.code"
                  :value="c.code"
                >
                  {{ c.code }} – {{ c.title }} ({{ c.sign }})
                </option>
              </select>
            </div>
          </div>
        </div>

        <div class="field">
          <div class="name for-checkbox">
            {{ $t("accountType.name") }}
          </div>
          <div
            class="value"
            style="line-height: 1.2;"
          >
            <div class="custom-mb-16">
              <label>
                <span class="wa-radio">
                  <input
                    v-model="model.is_imaginary"
                    type="radio"
                    value="0"
                  >
                  <span />
                </span>
                {{ $t("accountType.types.checking.name") }}
                <span class="hint">{{ $t("accountType.types.checking.message") }}</span>
              </label>
            </div>
            <div class="custom-mb-16">
              <label>
                <span class="wa-radio">
                  <input
                    v-model="model.is_imaginary"
                    type="radio"
                    value="1"
                  >
                  <span />
                </span>
                {{ $t("accountType.types.virtual.name") }}
                <span class="hint">{{ $t("accountType.types.virtual.message") }}</span>
              </label>
            </div>
            <div class="custom-mb-16">
              <label>
                <span class="wa-radio">
                  <input
                    v-model="model.is_imaginary"
                    type="radio"
                    value="-1"
                  >
                  <span />
                </span>
                {{ $t("accountType.types.virtualWithForecast.name") }}
                <span class="hint">{{ $t("accountType.types.virtualWithForecast.message") }}</span>
              </label>
            </div>

            <div class="custom-mb-16 ">
              <label>
                <span class="wa-radio">
                  <input
                    v-model="model.is_imaginary"
                    type="radio"
                    value="-2"
                    :disabled="!isPremium"
                  >
                  <span />
                </span>
                Подотчет
                <span class="hint">фывфывфыв</span>
                <div v-if="!isPremium">
                  <span class="hint text-red">только в премиум</span>
                </div>
              </label>
              <AddAccountAccountable
                v-if="isPremium"
                :accountable-contact-id.sync="model.accountable_contact_id"
                :disabled="model.is_imaginary !== '-2'"
              />
            </div>

            <p class="small custom-mb-16">
              <a
                href="#"
                target="_blank"
              ><b>{{ $t("accountTypeHint") }}</b></a>
            </p>
          </div>
        </div>

        <div
          v-if="!isModeUpdate"
          class="field"
        >
          <div class="name for-input">
            {{ $t("startingBalance") }}
          </div>
          <div class="value">
            <input-currency
              v-model="model.starting_balance"
              :currency-code="model.currency"
              :show-sign="false"
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
                />
                <div>
                  <a
                    href="#"
                    class="button nobutton smaller"
                    @click.prevent="model.icon = ''"
                  >{{ $t("delete") }}</a>
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
            />
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
            @click="submit('account')"
          >
            {{ isModeUpdate ? $t("update") : $t("add") }}
          </button>
          <button
            class="button light-gray"
            @click="() => { close() }"
          >
            {{ $t("cancel") }}
          </button>
        </div>
        <button
          v-if="isModeUpdate"
          :disabled="controlsDisabled"
          class="button outlined red desktop-and-tablet-only"
          @click="remove('account')"
        >
          <span>{{ $t("delete") }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { required } from 'vuelidate/dist/validators.min.js'
import updateEntityMixin from '@/mixins/updateEntityMixin'
import InputCurrency from '@/components/Inputs/InputCurrency'
import IconUploader from '@/components/Inputs/IconUploader'
import AddAccountAccountable from '@/components/Modals/AddAccountAccountable'

export default {

  components: {
    InputCurrency,
    IconUploader,
    AddAccountAccountable
  },

  mixins: [updateEntityMixin],

  props: ['editedItem'],

  data () {
    return {
      model: {
        id: null,
        name: '',
        currency: '',
        is_imaginary: '0',
        starting_balance: '',
        icon: '',
        description: '',
        accountable_contact_id: '0'
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
    },
    isPremium () {
      return window.appState.isPremium
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
