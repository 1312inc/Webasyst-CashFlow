<template>
  <div>
    <h2 class="custom-mb-32">
      {{ isModeUpdate ? $t("updateAccount") : $t("addAccount") }}
    </h2>

    <div class="fields custom-mb-32">
      <div class="field">
        <div class="name for-input">
          {{ $t("name") }}
        </div>
        <div class="value">
          <input
            v-model="model.name"
            ref="focus"
            class="bold"
            :class="{ 'state-error': $v.model.name.$error }"
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
              class="width-100"
              :class="{ 'state-error': $v.model.currency.$error }"
            >
              <option
                :value="c.code"
                v-for="c in $store.state.system.currencies"
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
            class="number shorter"
            :placeholder="0"
            type="text"
          />
          <span v-if="model.currency" class="custom-ml-8">{{
            $helper.currencySignByCode(model.currency)
          }}</span>
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
                :style="`background-image:url(${model.icon})`"
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

    <div class="flexbox">
      <div class="flexbox space-12 wide">
        <button @click="submit('account')" class="button purple">
          {{ isModeUpdate ? $t("update") : $t("add") }}
        </button>
        <button @click="close" class="button light-gray">
          {{ $t("cancel") }}
        </button>
      </div>
      <button v-if="isModeUpdate" @click="remove('account')" class="button red">
        {{ $t("delete") }}
      </button>
    </div>
  </div>
</template>

<script>
import { required } from 'vuelidate/lib/validators'
import updateEntityMixin from '@/mixins/updateEntityMixin'
import InputCurrency from '@/components/InputCurrency'
import IconUploader from '@/components/IconUploader'
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
  height: 40px;
  position: relative;

  &-image {
    width: 40px;
    height: 40px;
    border-radius: 0.25rem;
    background: {
      position: center center;
      size: cover;
    }
  }
}
</style>
